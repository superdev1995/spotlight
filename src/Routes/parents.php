<?php

$app->group('/parents', function () use ($app){
    $this->get('/confirm/{token_id}', function ($req, $res, $args) use ($app){
        $Child = new Child($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Confirm Pre-School Invitation';

        $invite = $Child->getInvite($args['token_id']);

        if(!$invite){
            $this->logger->info('Invalid token.', ['token_id' => $args['token_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'The invitation token is invalid.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $Child->setUserStatus($invite->child_id, $invite->user_id, 'A');

        $this->logger->info('Child user status updated.', ['child_id' => $invite->child_id, 'user_id' => $invite->user_id]);
        $this->flash->addMessage('success', 'You were added to the child’s profile.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
    })->setName('parentInvite');

    $this->post('/{child_id}', function ($req, $res, $args) use ($app){
        $Child  = new Child($this);
        $School = new School($this);
        $User   = new User($this);

        $data = $req->getParsedBody();

        if($req->getAttribute('csrf_status') === false){
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if(!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))){
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
        }

        if(!$data['email'] || !$args['child_id']){
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
        }

        $invited_user = $User->getOne($data['email'], 'P');

        if(!$invited_user){
            $this->logger->info('User not found.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'A parent with this email address does not seem to exist.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
        }

        if($invited_user->user_status != 'A'){
            $this->logger->info('Account not active.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);
            $this->flash->addMessage('danger', 'The the email address of this account has not been confirmed yet.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
        }

        if($Child->getParent($data['child_id'], $invited_user->user_id)){
            $this->logger->info('User already associated.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);
            $this->flash->addMessage('danger', 'This parent is already associated with the child.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
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
        $this->flash->addMessage('success', 'The parent or guarden was successfully invited.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
    })->setName('parentCreate');

    $this->post('/{child_id}/delete', function ($req, $res, $args) use ($app){
        $Child = new Child($this);

        $data = $req->getParsedBody();

        if($req->getAttribute('csrf_status') === false){
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if(!$data['user_id'] || !$args['child_id']){
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
        }

        $Child->purgeParent($args['child_id'], $data['user_id']);

        $this->logger->info('Parent deleted.', ['child_id' => $args['child_id'], 'user_id' => $data['user_id']]);
        $this->flash->addMessage('success', 'Parent association has been deleted.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
    })->setName('parentDelete');

    $this->get('/directory', function ($req, $res, $args) use ($app){
        $Child = new Child($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Parent Directory';


        $view['parents'] = $Child->getAllParentsForSchool($_SESSION['school_id']);


        return $this->view->render($res, 'parents.html', $view);
    })->setName('parentsDirectory');

});
