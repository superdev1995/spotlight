<?php

$app->group('/API/accidents', function() use($app) {
    /***************************************************************************
     * GET 'accidents/create'
     *
     * New accident submission view
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Child = new Child($this);
        $School = new School($this);

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (isset($_GET['child_id'])) {
            $view['preselected_child_id'] = (int)$_GET['child_id'];
        }

        $view['formdata'] = $this->flash->getMessages()['formdata'][0];

        $view['children'] = $Child->getAll($_SESSION['school_id']);

        $view['body_parts'] = $Accident->getBodyParts();
        
        $data=array('body_parts'=>$view['body_parts'],'children'=>$view['children'],'preselected_child_id' =>$view['preselected_child_id']);
        
        return $res->withJSON($data);
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

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (!$data['child_id'] || !$data['date'] || !$data['time'] || !$data['location'] || !$data['body_parts'] || !$data['description'] || !$data['cause']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data['date'])) {
            $this->logger->info('User submitted invalid date.');

            return $res->withJson(['error'=>'The date appears invalid. Please write as YYYY-MM-DD.']);
        }

        if (!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/", $data['time'])) {
            $this->logger->info('User submitted invalid time.');
            
            return $res->withJson(['error'=>'The time appears invalid. Please write as HH:mm.']);
        }
        
        $data['body_parts'] = implode(',', $data['body_parts']);

        $accident_id = $Accident->create($req->getAttribute('user_id'), $_SESSION['school_id'], $data);

        if (!$accident_id) {
            $this->logger->error('Accident::create failed.', [ 'user_id' => $req->getAttribute('user_id') ]);

            return $res->withJson(['error'=>'The accident could not be created.']);
        }

        $Timeline->create($req->getAttribute('user_id'), $data['child_id'], 'accident', $accident_id, 1);

        return $res->withJSON(['success'=>'Accident created.']);
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

        $view['accident'] = $Accident->getOne($args['accident_id']);

        if (!$view['accident']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            $child_user = $Child->getParent($view['accident']->child_id, $req->getAttribute('user_id'));

            if (!$child_user) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);

                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        }

        /**
         * This is to prevent users from loading any accident ID in the URI.
         */
        if ($view['accident']->school_id != $_SESSION['school_id']) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $view['child'] = $Child->getOne($view['accident']->child_id);
        $view['teacher'] = $User->getOne($view['accident']->user_id);
        $view['logs'] = $Accident->getLogs($view['accident']->accident_id);

        $data=array('logs'=>$view['logs'],'accident'=>$view['accident'],'teacher'=>$view['teacher'],'child'=>$view['child']);
        return $res->withJson($data);
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

        $accident = $Accident->getOne($args['accident_id']);

        if ($data['recipient'] == 'T') {
            $users = $School->getActiveUsers($accident->school_id);
        } elseif ($data['recipient'] == 'P') {
            $users = $Child->getActiveParents($accident->child_id);
        } else {
            $this->logger->info('User provided an invalid recipient.', ['user_id' => $req->getAttribute('user_id'), 'recipient' => $data['recipient']]);

            return $res->withJson(['error'=>'You provided an invalid group of recipients.']);
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

        return $res->withJson(['success'=>'The recipients were informed about this accident.']);
    })->setName('accidentAnnounce');
});