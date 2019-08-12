<?php

$app->group('/accidents', function() use($app) {
    /***************************************************************************
     * GET 'accidents/create'
     *
     * New accident submission view
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Child = new Child($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Report Accident';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (isset($_GET['child_id'])) {
            $view['preselected_child_id'] = (int)$_GET['child_id'];
        }

        if(isset($this->flash->getMessages()['formdata'][0])){
            $view['formdata'] = $this->flash->getMessages()['formdata'][0];
            unset($view['flash']['formdata']);
        }

        $view['children'] = $Child->getAll($_SESSION['school_id']);

        $view['body_parts'] = $Accident->getBodyParts();

        return $this->view->render($res, 'accidentCreate.html', $view);
    })->setName('accidentCreate');

    /***************************************************************************
     * POST 'accidents/create'
     *
     * Validate create accident form and redirect
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Child = new Child($this);
        $School = new School($this);
        $Timeline = new Timeline($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }


        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$data['child_id'] || !$data['date'] || !$data['time'] || !$data['location'] || !$data['body_parts'] || !$data['description'] || !$data['cause']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentCreate'));
        }

        $data['body_parts'] = implode(',', $data['body_parts']);

        $accident_id = $Accident->create($req->getAttribute('user_id'), $_SESSION['school_id'], $data);

        if (!$accident_id) {
            $this->logger->error('Accident::create failed.', [ 'user_id' => $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'The accident could not be created.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentCreate'));
        }

        $Timeline->create($req->getAttribute('user_id'), $data['child_id'], 'accident', $accident_id, 1);

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentDetails', [accident_id => $accident_id]));
    });

    /***************************************************************************
     * GET 'accidents/:accident_id'
     *
     * View an accident item
     **************************************************************************/
    $this->get('/{accident_id}', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Child = new Child($this);
        $School = new School($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Accident Details';

        $view['accident'] = $Accident->getOne($args['accident_id']);

        if (!$view['accident']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        } else {
            $child_user = $Child->getParent($view['accident']->child_id, $req->getAttribute('user_id'));

            if (!$child_user) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        }

        /**
         * This is to prevent users from loading any accident ID in the URI.
         */
        if ($view['accident']->school_id != $_SESSION['school_id']) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['signed'] = false;
        if ($view['accident']->signed_by_user || $view['accident']->signature || $view['accident']->signed_by) {
            $view['signed'] = true;
        }

        $view['child'] = $Child->getOne($view['accident']->child_id);
        $view['teacher'] = $User->getOne($view['accident']->user_id);
        $view['logs'] = $Accident->getLogs($view['accident']->accident_id);

        return $this->view->render($res, 'accidentDetails.html', $view);
    })->setName('accidentDetails');

    /***************************************************************************
     * POST 'accidents/:accident_id/announce'
     *
     * Send announcement to selected groups
     **************************************************************************/
    $this->post('/{accident_id}/announce', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $accident = $Accident->getOne($args['accident_id']);

        if ($data['recipient'] == 'T') {
            $users = $School->getActiveUsers($accident->school_id);
        } elseif ($data['recipient'] == 'P') {
            $users = $Child->getActiveParents($accident->child_id);
        } else {
            $this->logger->info('User provided an invalid recipient.', ['user_id' => $req->getAttribute('user_id'), 'recipient' => $data['recipient']]);
            $this->flash->addMessage('danger', 'You provided an invalid group of recipients.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentDetails', [accident_id => $args['accident_id']]));
        }

        foreach ($users as $user) {
            $this->mailer->send('accidentAnnounce.html', [
                'to' => $user->user_email,
                'subject' => 'Accident reported in preschool',
                'first_name' => $user->user_first_name,
                'message' => $data['message'],
                'accident' => $Accident->getOne($args['accident_id']),
                'school' => $School->getOne($accident->school_id),
            ]);

            $this->logger->info('Announced accident to recipient.', [ 'email', $user->user_email ]);
        }

        $Accident->createLog($req->getAttribute('user_id'), $args['accident_id'], $data);

        $this->flash->addMessage('success', 'The recipients were informed about this accident.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentDetails', [accident_id => $args['accident_id']]));
    })->setName('accidentAnnounce');

    /***************************************************************************
     * POST 'accidents/:accident_id/signature'
     *
     * Send parent's signature
     **************************************************************************/
    $this->post('/{accident_id}/signature', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Child = new Child($this);
        $School = new School($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $accident = $Accident->getOne($args['accident_id']);

        if (!$accident) {
            $this->logger->info('Accident not found', ['user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'Accident not found');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $parent = $Child->getParent($accident->child_id, $req->getAttribute('user_id'));

        if (!$parent) {
            $this->logger->info('User is not a parent of this child', ['user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentDetails', [accident_id => $args['accident_id']]));
        }

        if ($accident->signed_by_user || $accident->signature || $accident->signed_by){
            $this->logger->info('Accident was signed before', ['user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentDetails', [accident_id => $args['accident_id']]));
        }

       $Accident->sign($args['accident_id'], $req->getAttribute('user_id'), $data['signature_name'], $data['signature_data']);

        $this->logger->info('Accident signed by user .', [ 'user', $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'Signature was sent');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('accidentDetails', [accident_id => $args['accident_id']]));
    })->setName('accidentSignature');
});
