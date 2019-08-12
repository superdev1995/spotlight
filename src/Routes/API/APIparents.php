<?php

$app->group('/API/parents', function () use ($app){
    /*$this->get('/confirm/{token_id}', function ($req, $res, $args) use ($app){
        $Child = new Child($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Confirm Pre-School Invitation';

        $invite = $Child->getInvite($args['token_id']);

        if(!$invite){
            $this->logger->info('Invalid token.', ['token_id' => $args['token_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'The invitation token is invalid.');
        }

        $Child->setUserStatus($invite->child_id, $invite->user_id, 'A');

        $this->logger->info('Child user status updated.', ['child_id' => $invite->child_id, 'user_id' => $invite->user_id]);
        $this->flash->addMessage('success', 'You were added to the child’s profile.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
    })->setName('parentInvite');*/

    /***************************************************************************
     * POST 'parents/:child_id'
     *
     * Send email for add parent
     **************************************************************************/
    $this->post('/{child_id}', function ($req, $res, $args) use ($app){
        $Child  = new Child($this);
        $School = new School($this);
        $User   = new User($this);

        $data = $req->getParsedBody();
        
        if(!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))){
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if(!$data['email'] || !$args['child_id']){
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        $invited_user = $User->getOne($data['email'], 'P');

        if(!$invited_user){
            $this->logger->info('User not found.', ['email' => $data['email']]);

            return $res->withJson(['error'=>'A parent with this email address does not seem to exist.']);
        }

        if($invited_user->user_status != 'A'){
            $this->logger->info('Account not active.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);

            return $res->withJson(['error'=>'The the email address of this account has not been confirmed yet.']);
        }

        if($Child->getParent($data['child_id'], $invited_user->user_id)){
            $this->logger->info('User already associated.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);

            return $res->withJson(['error'=>'This parent is already associated with the child.']);
        }

        $child  = $Child->getOne($args['child_id']);
        $school = $School->getOne($child->school_id);

        $this->mailer->send('parentInvite.html', [
            'to'         => $invited_user->user_email,
            'subject'    => $req->getAttribute('user')->user_first_name . ' ' . $req->getAttribute('user')->user_last_name . ' shared a child profile with you',
            'first_name' => $invited_user->user_first_name,
            'child'      => $child,
            'school'     => $school,
            'token_id'   => $Child->createParent($args['child_id'], $invited_user->user_id),
        ]);

        $this->logger->info('Parent invited.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);

        return $res->withJson(['success'=>'The parent or guarden was successfully invited.']);
    })->setName('parentCreate');

    /***************************************************************************
     * POST 'parents/:child_id/delete'
     *
     * Delete parent  on child
     **************************************************************************/
    $this->post('/{child_id}/delete', function ($req, $res, $args) use ($app){
        $Child = new Child($this);

        $data = $req->getParsedBody();

        if(!$data['user_id'] || !$args['child_id']){
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        $Child->purgeParent($args['child_id'], $data['user_id']);

        $this->logger->info('Parent deleted.', ['child_id' => $args['child_id'], 'user_id' => $data['user_id']]);

        return $res->withJson(['success'=>'Parent association has been deleted.']);
    })->setName('parentDelete');

    /*$this->get('/directory', function ($req, $res, $args) use ($app){
        $Child = new Child($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Parent Directory';


        $view['parents'] = $Child->getAllParentsForSchool($_SESSION['school_id']);


        return $this->view->render($res, 'parents.html', $view);
    })->setName('parentsDirectory');*/

});
