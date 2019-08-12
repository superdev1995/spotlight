<?php

$app->group('/login', function() use($app) {
    /***************************************************************************
     * GET 'login'
     *
     * View login form
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Account Login';

        return $this->view->render($res, 'login.html', $view);
    })->setName('login');

    /***************************************************************************
     * POST 'login'
     *
     * Authenticate user upon login
     **************************************************************************/
    $this->post('', function($req, $res, $args) use($app) {
        $ActivationToken = new ActivationToken($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$data['email'] || !$data['password'] || !$data['type']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
        }

        if (!StringHandler::validateEmail($data['email'])) {
            $this->logger->info('Email address is invalid.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The email address '.$data['email'].' is invalid.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
        }

        $user = $User->getOne($data['email'], $data['type']);

        if (!$user) {
            $this->logger->info('Account with the credentials was not found.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'An account with these credentials was not found.');

            sleep(2);

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
        }

        if (!password_verify($data['password'], $user->user_password)) {
            $this->logger->info('Password verification failed.', ['user_id' => $user->user_id]);
            $this->flash->addMessage('danger', 'An account with these credentials was not found.');

            sleep(2);

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
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
            $this->flash->addMessage('success', 'Your account is pending activation. Please check your inbox for instructions.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
        }

        if ($user->user_status == 'D') {
            $this->logger->info('Login rejected because the account is deactivated.', ['user_id' => $user->user_id]);
            $this->flash->addMessage('danger', 'This account is deactivated.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
        }

        try {
            $Auth = new Auth($this);

            $Auth->createSession($user->user_id, isset($data['remember_me']));

            $this->logger->info('User logged in.', ['user_id' => $user->user_id]);
        } catch (Exception $e) {
            $this->logger->error('User log in failed. '.$e->getMessage(), ['user_id' => $user->user_id]);
            $this->flash->addMessage('danger', 'Log in attempt failed.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
        }
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
    });

    /***************************************************************************
     * GET 'login/recover'
     *
     * View password recovery form
     **************************************************************************/
    $this->get('/recover', function($req, $res, $args) use($app) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Recover Password';

        return $this->view->render($res, 'loginRecover.html', $view);
    })->setName('loginRecover');

    /***************************************************************************
     * POST 'login/recover'
     *
     * Send out an email with a token to allow change of password
     **************************************************************************/
    $this->post('/recover', function($req, $res, $args) use ($app) {
        $ActivationToken = new ActivationToken($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!StringHandler::validateEmail($data['email'])) {
            $this->logger->info('Email address is invalid.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The email address '.$data['email'].' is invalid.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('loginRecover'));
        }

        $user = $User->getOne($data['email'], $data['type']);

        if (!$user) {
            $this->logger->info('Account with email address was not found.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'An account with these credentials was not found.');

            sleep(1);

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('loginRecover'));
        }

        if ($user->status == 'D') {
            $this->logger->info('Reset rejected because the account is deactivated.', ['user_id' => $user->user_id]);
            $this->flash->addMessage('danger', 'This account is deactivated.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('loginRecover'));
        }

        $auth_token_id = $ActivationToken->createToken($user->user_id);

        $this->logger->debug('ActivationToken created.', ['user_id' => $user->user_id, 'token_id' => $auth_token_id]);

        $this->mailer->send('loginRecover.html', [
            'to' => $user->user_email,
            'subject' => 'Password reset instruction',
            'first_name' => $user->user_first_name,
            'token_id' => $auth_token_id,
        ]);

        $this->logger->info('Recovery email sent.', ['email' => $user->user_email]);
        $this->flash->addMessage('success', 'A password reset token has been sent to <strong>'.$user->user_email.'</strong>.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
    });

    /***************************************************************************
     * GET 'login/reset/:token_id'
     *
     * View password reset form if token is provided
     **************************************************************************/
    $this->get('/reset/{token_id}', function($req, $res, $args) use($app) {
        $ActivationToken = new ActivationToken($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Reset Password';

        $this->logger->debug('ActivationToken provided.', ['token_id' => $args['token_id']]);

        $auth_token = $ActivationToken->getToken($args['token_id']);

        if ($auth_token) {
            $view['token'] = $auth_token;

            return $this->view->render($res, 'loginReset.html', $view);
        } else {
            $this->logger->notice('ActivationToken invalid.', ['token_id' => $args['token_id']]);
            $this->flash->addMessage('danger', 'The password reset token is invalid.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('loginRecover'));
        }

        return $this->view->render($res, 'loginRecover.html', $view);

    })->setName('loginReset');

    /***************************************************************************
     * POST 'login/reset/:token_id'
     *
     * Validate password reset form
     **************************************************************************/
    $this->post('/reset/{token_id}', function($req, $res, $args) use($app) {
        $ActivationToken = new ActivationToken($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $this->logger->debug('ActivationToken provided.', ['token_id' => $args['token_id']]);

        $auth_token = $ActivationToken->getToken($args['token_id']);

        if ($auth_token) {
            if (strcmp($data['password1'], $data['password2']) !== 0) {
                $this->logger->info('Passwords do not match.', ['token_id' => $auth_token->id, 'user_id' => $auth_token->user_id]);
                $this->flash->addMessage('danger', 'The two passwords are not identical.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('loginReset', ['token_id' => $args['token_id']]));
            }

            if (strlen($data['password1']) < 6) {
                $this->logger->info('Password too short.', ['token_id' => $auth_token->id, 'user_id' => $auth_token->user_id]);
                $this->flash->addMessage('danger', 'The password must have at least 6 characters.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('loginReset', ['token_id' => $args['token_id']]));
            }

            $User->setPassword($auth_token->user_id, $data['password1']);

            $this->logger->debug('Password reset.', ['token_id' => $auth_token->id, 'user_id' => $auth_token->user_id]);
            $this->flash->addMessage('success', 'The password has been successfully reset.');

            $ActivationToken->purgeToken($auth_token->id);

            $this->logger->debug('ActivationToken purged.', ['token_id' => $auth_token->id, 'user_id' => $auth_token->user_id]);
        } else {
            $this->logger->notice('ActivationToken invalid.', ['token_id' => $args['token_id']]);
            $this->flash->addMessage('danger', 'The password reset token is invalid.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
    });
});
