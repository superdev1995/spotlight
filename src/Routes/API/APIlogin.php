<?php

$app->group('/API', function() use($app) {
    /***************************************************************************
     * POST ' login'
     *
     * Authenticate user upon login
     * 
     * Param: email, password,  type
     **************************************************************************/
    $this->post('/login', function($req, $res, $args) use($app) {
        $ActivationToken = new ActivationToken($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if (!$data['email'] || !$data['password'] || !$data['type']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        if (!StringHandler::validateEmail($data['email'])) {
            $this->logger->info('Email address is invalid.', ['email' => $data['email']]);

            return $res->withJson(['error'=>'Email address is invalid.']);
        }

        $user = $User->getOne($data['email'], $data['type']);

        if (!$user) {
            $this->logger->info('Account with the credentials was not found.', ['email' => $data['email']]);

            sleep(2);
            return $res->withJson(['error'=>'An account with these credentials was not found.']);
        }

        if (!password_verify($data['password'], $user->user_password)) {
            $this->logger->info('Password verification failed.', ['user_id' => $user->user_id]);

            sleep(2);
            return $res->withJson(['error'=>'An account with these credentials was not found.']);
        }

        $auth_token_id = $ActivationToken->createToken($user->user_id);

        if ($user->user_status == 'P') {
            $this->mailer->send('registerConfirm.html', [
                'to' => $user->user_email,
                'subject' => 'Please confirm your email address',
                'first_name' => $user->user_first_name,
                'token_id' => $auth_token_id,
            ]);

            sleep(1);

            $this->logger->info('Login rejected because the account is pending activation.', ['user_id' => $user->user_id]);

            return $res->withJson(['error'=>'Your account is pending activation. Please check your inbox for instructions.']);
        }

        if ($user->user_status == 'D') {
            $this->logger->info('Login rejected because the account is deactivated.', ['user_id' => $user->user_id]);

            return $res->withJson(['error'=>'This account is deactivated.']);
        }

        try {
            $Auth = new Auth($this);

            $Auth->createSession($user->user_id, isset($data['remember_me']));

            $this->logger->info('User logged in.', ['user_id' => $user->user_id]);
        } catch (Exception $e) {
            $this->logger->error('User log in failed. '.$e->getMessage(), ['user_id' => $user->user_id]);

            return $res->withJson(['error'=>'Log in attempt failed.']);
        }

        return $res->withJson($user);
    });

});
