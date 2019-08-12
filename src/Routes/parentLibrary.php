<?php

$app->group('/parentLibrary', function() use($app) {
    /***************************************************************************
	 * GET 'parentLibrary/'
	 *
	 * 
	 **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Library = new ParentLibrary($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Parent Library';

        if ($req->getAttribute('user')->user_type == 'P'){
            $view['school_id_parent'] = $School->getSchoolID( $req->getAttribute('user_id'), $_SESSION['child_id'] )->school_id;
            $room_id_parent = $Library->getRoom($_SESSION['child_id'])[0]->room_id;
            $view['libraries'] = $Library->librariesInRoom($room_id_parent);
            $view['school_user'] = $School->getUser($view['school_id_parent'], $req->getAttribute('user_id'));
        }
        else{
            $view['libraries'] = $Library->getAll($_SESSION['school_id']);
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        }

        if(!$view['school_user']){
            $view['school_user'] = $School->getParent( $req->getAttribute('user_id'));
        }

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        return $this->view->render($res, 'parentLibrary.html', $view);
    })->setName('parentLibrary');

    /***************************************************************************
	 * POST 'parentLibrary/'
	 *
	 * 
	 **************************************************************************/
    $this->post('', function($req, $res, $args) use($app) {
        $Library = new ParentLibrary($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Parent Library';
        $view['sharelibs'] = $Library->shareCheck($_SESSION['school_id']);

        foreach ($view['sharelibs'] as $y){
            if (isset($_POST[$y->room_id])){
                $Library->shareInRoom($_POST['libID'],$y->room_id);
            }
            else{
                $Library->unshareInRoom($_POST['libID'],$y->room_id);
            }
        }

        $view['libraries'] = $Library->getAll($_SESSION['school_id']);

        return $this->view->render($res, 'parentLibrary.html', $view);
    })->setName('parentLibrary');

    /***************************************************************************
	 * GET 'parentLibrary/create'
	 *
	 * View parentLibrary form
	 **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        //$Library = new ParentLibrary($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Contribute to the TeachKloud Parent Library';

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
        // $view['categories'] = $Library->getCategories();
        // $view['ages'] = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        return $this->view->render($res, 'parentLibraryCreate.html', $view);
    })->setName('parentLibraryCreate');

    /***************************************************************************
	 * POST 'parentLibrary/create'
	 *
	 * Save parentLibrary form
	 **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Library = new ParentLibrary($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        /* if (!$data['file']) {
             $this->logger->info('User forgot to upload a file.');
             $this->flash->addMessage('danger', 'Please upload and wait for your file to finish uploading before saving.');

             return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('parentLibraryCreate'));
         } */

        if (!$data['libName'] || !$data['libDesc']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'Please provide a name and description for your library entry before saving.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('parentLibraryCreate'));
        }

        $library_id = $Library->create($req->getAttribute('user_id'),/* $file->getUrl(),*/ $data, $_SESSION['school_id']);

        $this->logger->info('Library entry  created.', [ 'library_id' => $library_id, 'user_id' => $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'The library entry has been submitted for approval.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('parentLibrary'));
    });

    /***************************************************************************
	 * GET 'parentLibrary/:library_id'
	 *
	 * 
	 **************************************************************************/
    $this->get('/{library_id}', function($req, $res, $args) use($app) {
        $Library = new ParentLibrary($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Parent Library';

        $view['library'] = $Library->getOne($args['library_id']);

        if ($req->getAttribute('user')->user_type == 'P'){
            $view['school_id_parent'] = $School->getSchoolID( $req->getAttribute('user_id'), $_SESSION['child_id'] )->school_id;
            $room_id_parent = $Library->getRoom($_SESSION['child_id'])[0]->room_id;
            $view['libraries'] = $Library->librariesInRoom($room_id_parent);
            $view['school_user'] = $School->getUser($view['school_id_parent'], $req->getAttribute('user_id'));
        }
        else{
            $view['libraries'] = $Library->getAll($_SESSION['school_id']);
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        }

        if(!$view['school_user']){
            $view['school_user'] = $School->getParent( $req->getAttribute('user_id'));
        }

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$view['library']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        /*if ($view['resource']->resource_status != 'A') {
            if ($view['library']->user_id != $req->getAttribute('user_id')) {
                $this->logger->notice('Wrong resource owner.', [ 'library_id' => $args['library_id'], 'user_id' => $req->getAttribute('user_id') ]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withRedirect($this->router->pathFor('parentLibrary'));
            }
        }*/

        return $this->view->render($res, 'parentLibraryDetails.html', $view);
    })->setName('parentLibraryDetails');

    /***************************************************************************
	 * GET 'parentLibrary/:library_id/donwload'
	 *
	 * 
	 **************************************************************************/
    $this->get('/{library_id}/download', function($req, $res, $args) use($app) {
        $Library = new ParentLibrary($this);
        $School = new School($this);

        $library = $Library->getOne($args['library_id']);

        if (!$library) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        /*if ($library->resource_status != 'A') {
            if ($view['library']->user_id != $req->getAttribute('user_id')) {
                $this->logger->notice('Wrong resource owner.', [ 'resource_id' => $args['resource_id'], 'user_id' => $req->getAttribute('user_id') ]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withRedirect($this->router->pathFor('resource'));
            }
        }*/

        if (!$School->getSubscriptionStatus($_SESSION['school_id'])) {
            $this->logger->notice('Download rejected.', [ 'resource_id' => $args['resource_id'], 'school_id' => $_SESSION['school_id'] ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('resource'));
        }

        $Library->setDownloads($args['library_id']);

        return $res->withStatus(302)->withRedirect($library->library_url);
    })->setName('parentLibraryDownload');

    /***************************************************************************
	 * GET 'parentLibrary/:library_id/share'
	 *
	 * 
	 **************************************************************************/
    $this->get('/{library_id}/share', function($req, $res, $args) use($app) {
        $Library = new ParentLibrary($this);
        $School = new School($this);
        $Room = new Room($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Parent Library Sharing';

        $view['library'] = $Library->getOne($args['library_id']);
        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['subscription_status'] = $School->getSubscriptionStatus($_SESSION['school_id']);
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['sharedRooms'] = $Library->isSharedInRoom($args['library_id']);

        $z = array();

        foreach ($view['sharedRooms'] as $x){
            foreach ($x as $y){
                array_push($z,$y);
            }
        }

        $view['sharedArr'] = $z;

        if (!$view['library']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1){
            $this->logger->notice('Wrong resource owner.', [ 'library_id' => $args['library_id'], 'user_id' => $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('parentLibrary'));
        }

        return $this->view->render($res, 'parentLibraryShare.html', $view);
    })->setName('parentLibraryShare');
});