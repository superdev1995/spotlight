<?php

$app->group('/resources', function() use($app) {
    /***************************************************************************
     * GET 'resources'
     *
     * View all resources
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Resource = new Resource($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Resource Library';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['resources'] = $Resource->getAll();
        $view['categories'] = $Resource->getCategories();
        $view['ages'] = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, ];

        return $this->view->render($res, 'resource.html', $view);
    })->setName('resource');

    /***************************************************************************
     * GET 'resources/create'
     *
     * View create resources form
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Resource = new Resource($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Contribute to the TeachKloud Resource Library';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if(isset($this->flash->getMessages()['formdata'][0])){
            $view['formdata'] = $this->flash->getMessages()['formdata'][0];
            unset($view['flash']['formdata']);
        }
        $view['categories'] = $Resource->getCategories();
        $view['ages'] = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        return $this->view->render($res, 'resourceCreate.html', $view);
    })->setName('resourceCreate');

    /***************************************************************************
     * GET 'stories/create'
     *
     * Save create resources form
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Resource = new Resource($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$data['file']) {
            $this->logger->info('User forgot to upload a file.');
            $this->flash->addMessage('danger', 'Please upload and wait for your file to finish uploading before saving.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('resourceCreate'));
        }

        if (!$data['name'] || !$data['description']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'Please provide a name and description for your resource before saving.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('resourceCreate'));
        }

        if (!$data['min_age'] || !$data['max_age'] || !$data['categories']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'Please provide a learning opportunity and a suitable age range.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('resourceCreate'));
        }

        $categories = implode(',', $data['categories']);
        $file = $this->uploader->getFile($data['file']);

        $resource_id = $Resource->create($req->getAttribute('user_id'), $categories, $file->getUrl(), $data);

        if ($resource_id) {
            $this->mailer->send('resourceApprove.html', [
                'to' => 'support@teachkloud.com',
                'subject' => 'A new learning resource is pending approval',
                'first_name' => $req->getAttribute('user')->user_first_name,
                'last_name' => $req->getAttribute('user')->user_last_name,
                'resource_id' => $resource_id,
            ]);

            $file->store();
        } else {
            $this->logger->error('Missing resource_id.', [ 'user_id' => $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'The resource could not be created.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('resource'));
        }

        $this->logger->info('Resource created.', [ 'resource_id' => $resource_id, 'user_id' => $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'The learning resource has been submitted for approval.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('resource'));
    });

    /***************************************************************************
     * GET 'resources/:resource_id'
     *
     * 
     **************************************************************************/
    $this->get('/{resource_id}', function($req, $res, $args) use($app) {
        $Resource = new Resource($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Resources Library';

        $view['resource'] = $Resource->getOne($args['resource_id']);
        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['subscription_status'] = $School->getSubscriptionStatus($_SESSION['school_id']);

        if (!$view['resource']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($view['resource']->resource_status != 'A') {
            if ($view['resource']->user_id != $req->getAttribute('user_id')) {
                $this->logger->notice('Wrong resource owner.', [ 'resource_id' => $args['resource_id'], 'user_id' => $req->getAttribute('user_id') ]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withRedirect($this->router->pathFor('resource'));
            }
        }

        return $this->view->render($res, 'resourceDetails.html', $view);
    })->setName('resourceDetails');

    /***************************************************************************
     * GET 'stories/:resource_id/download'
     *
     * 
     **************************************************************************/
    $this->get('/{resource_id}/download', function($req, $res, $args) use($app) {
        $Resource = new Resource($this);
        $School = new School($this);

        $resource = $Resource->getOne($args['resource_id']);

        if (!$resource) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($resource->resource_status != 'A') {
            if ($view['resource']->user_id != $req->getAttribute('user_id')) {
                $this->logger->notice('Wrong resource owner.', [ 'resource_id' => $args['resource_id'], 'user_id' => $req->getAttribute('user_id') ]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withRedirect($this->router->pathFor('resource'));
            }
        }

        if (!$School->getSubscriptionStatus($_SESSION['school_id'])) {
            $this->logger->notice('Download rejected.', [ 'resource_id' => $args['resource_id'], 'school_id' => $_SESSION['school_id'] ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('resource'));
        }

        $Resource->setDownloads($args['resource_id']);

        return $res->withStatus(302)->withRedirect($resource->resource_url);
    })->setName('resourceDownload');
});
