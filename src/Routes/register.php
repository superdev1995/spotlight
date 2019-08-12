<?php

$app->group('/register', function() use($app) {
    $this->get('', function($req, $res, $args) use($app) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Create Account';

        return $this->view->render($res, 'register.html', $view);
    })->setName('register');

    $this->post('', function($req, $res, $args) use($app) {
        $ActivationToken = new ActivationToken($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$data['email'] || !$data['password1'] || !$data['password2'] || !$data['first_name'] || !$data['last_name']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        if (!$data['terms']) {
            $this->logger->info('User did not agree to the terms.');
            $this->flash->addMessage('danger', 'Please agree to the Terms &amp; Conditions.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        if (!StringHandler::validateEmail($data['email'])) {
            $this->logger->info('Email address is invalid.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The email address is invalid.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        $user = $User->getOne($data['email'], $data['type']);

        if ($user) {
            $this->logger->info('Account already exists.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The email address is already registered.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        if (strcmp($data['password1'], $data['password2']) !== 0) {
            $this->logger->info('Passwords do not match.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The two passwords are not identical.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        if (strlen($data['password1']) < 6) {
            $this->logger->info('Password too short.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The password must have at least 6 characters.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        if ($User->getOne($data['email'])) {
            $this->logger->info('Email address already exists.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'An account for <strong>'.$data['email'].'</strong> already exists.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        $data['email'] = filter_var(strtolower($data['email']), FILTER_SANITIZE_EMAIL);

        $user_id = $User->create($data);

        if (!$user_id) {
            $this->logger->error('User::createUser failed.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'Your account could not be created.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        $this->mailer->send('registerConfirm.html', [
            'to' => $data['email'],
            'subject' => 'Please confirm your email address',
            'first_name' => $data['first_name'],
            'token_id' => $ActivationToken->createToken($user_id),
        ]);

        $this->logger->info('Account created.', ['email' => $data['email'], 'user_id' => $user_id]);
        $this->flash->addMessage('success', 'Welcome! Please check your email at <strong>'.$data['email'].'</strong>.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('registerWelcome'));
    });

    $this->get('/welcome', function($req, $res, $args) use($app) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Welcome';

        return $this->view->render($res, 'registerWelcome.html', $view);
    })->setName('registerWelcome');

    $this->get('/confirm/{token_id}', function($req, $res, $args) use($app) {
        $ActivationToken = new ActivationToken($this);
        $ListHandler = new ListHandler($this);
        $User = new User($this);

        $token = $ActivationToken->getToken($args['token_id']);

        if ($token) {
            /**
             * Set user status to A for 'active'.
             */
            if ($User->setStatus($token->user_id, 'A')) {
                $this->logger->info('User status updated.', ['user_id' => $token->user_id]);
                $this->flash->addMessage('success', 'Thank you! Your account has been confirmed.');

                $user = $User->getOne($token->user_id);

                /**
                 * Add email address to the newsletter.
                 */
                $ListHandler->createUser($user, $user->user_type);

                /**
                 * Send a welcome email.
                 */
                $this->mailer->send('registerWelcome.html', [
                    'to' => $user->user_email,
                    'subject' => 'Welcome to TeachKloud',
                    'first_name' => $user->user_first_name,
                ]);

                $this->logger->debug('Register welcome email sent.', [ 'email' => $user->user_email ]);

                $attachment_path = realpath(__DIR__ . '/../../public/downloads/getting-started.pdf');

                $this->mailer->send('registerGettingStarted.html', [
                    'to' => $user->user_email,
                    'subject' => 'Getting Started with TeachKloud',
                    'first_name' => $user->user_first_name,
                    'attachment' => $attachment_path,
                ]);

                $this->logger->debug('Register getting started email sent.', [ 'email' => $user->user_email ]);

                /**
                 * Delete the token that has just been used.
                 */
                $ActivationToken->purgeToken($token->id);
            } else {
                $this->logger->error('User::setStatus failed.', ['user_id' => $token->user_id]);
                $this->flash->addMessage('danger', 'Could not activate your account.');
            }
        } else {
            $this->logger->notice('ActivationToken invalid.', ['token_id' => $args['token_id']]);
            $this->flash->addMessage('danger', 'The activation token is invalid.');
        }

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('login'));
    })->setName('registerConfirm');
});
