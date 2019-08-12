<?php

$app->group('/account', function() use($app) {
    /***************************************************************************
     * GET 'account'
     *
     * Account settings view
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Account Settings';

        return $this->view->render($res, 'account.html', $view);
    })->setName('account');

    /***************************************************************************
     * POST 'account/edit'
     *
     * Validate account settings form
     **************************************************************************/
    $this->post('/edit', function($req, $res, $args) use($app) {
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if (!$data['first_name'] || !$data['last_name']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
        }

        $User->setDetails($req->getAttribute('user_id'), $data);

        /**
         * Only process this if the user has actually provided a new avatar.
         */
        if ($data['avatar']) {
            $avatar = $this->uploader->getFile($data['avatar']);

            if ($avatar->data['is_image']) {
                if ($req->getAttribute('user')->user_avatar_url) {
                    $this->logger->debug('Attempt to delete old avatar.', [ 'url' => $req->getAttribute('user')->user_avatar_url ]);

                    /**
                     * Delete the existing avatar if there is any found in the
                     * user_avatar_url column to save disk space.
                     */
                    $old_avatar = $this->uploader->getFile($req->getAttribute('user')->user_avatar_url);
                    $old_avatar->delete();
                }

                /**
                 * Save the resized avatar only. We never know how big the
                 * original file size is and clutter the CDN disk space.
                 */
                $resized_avatar = $this->uploader->createLocalCopy($avatar->getUrl());
                $resized_avatar->store();

                $avatar->delete();

                $User->setAvatar($req->getAttribute('user_id'), $resized_avatar->getUrl());
            }
        }

        $this->logger->info('Account details updated.', [ 'user_id' => $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'Your account details have been updated.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
    })->setName('accountEdit');

    /***************************************************************************
     * POST 'account/password'
     *
     * Validate new password form
     **************************************************************************/
    $this->post('/password', function($req, $res, $args) use($app) {
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $user = $User->getOne($req->getAttribute('user_id'));

        if (strcmp(password_hash($data['password'], PASSWORD_DEFAULT), $user->user_password) !== 0) {
            $this->logger->info('Current password wrong.');
            $this->flash->addMessage('danger', 'The current password is wrong.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
        }

        if (!$data['password1'] || !$data['password2'] || !$data['password']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
        }

        if (strcmp($data['password1'], $data['password2']) !== 0) {
            $this->logger->info('Passwords do not match.');
            $this->flash->addMessage('danger', 'The two new passwords are not identical.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        if (strlen($data['password1']) < 6) {
            $this->logger->info('Password too short.');
            $this->flash->addMessage('danger', 'The password must have at least 6 characters.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('register'));
        }

        $User->setPassword($req->getAttribute('user_id'), $data['password']);

        $this->logger->info('Account password updated.', [ 'user_id' => $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'Your password has been updated.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
    })->setName('accountPassword');

    /***************************************************************************
     * POST 'accidents/email'
     *
     * Validate new email form
     **************************************************************************/
    $this->post('/email', function($req, $res, $args) use($app) {
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if (!StringHandler::validateEmail($data['email'])) {
            $this->logger->info('Email address is invalid.', ['email' => $data['email']]);
            $this->flash->addMessage('danger', 'The email address is invalid.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
        }

        $user = $User->getOne($req->getAttribute('user_id'));

        $data['email'] = filter_var(strtolower($data['email']), FILTER_SANITIZE_EMAIL);

        /**
         * Change the email in the newsletter as well.
         */
        ListHandler::changeEmail($user->user_email, $data['email'], $user->user_type);

        /**
         * We did not implement a double opt-in validation, so we are just
         * taking the user by its word. Double opt-in should be enhanced and
         * implemented at a later time.
         */
        $User->setEmail($req->getAttribute('user_id'), $data['email']);

        $this->logger->info('Account email updated.', [ 'user_id' => $req->getAttribute('user_id'), 'email' => $data['email'] ]);
        $this->flash->addMessage('success', 'Your email address has been updated.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('account'));
    })->setName('accountEmail');
});
