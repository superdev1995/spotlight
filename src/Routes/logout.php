<?php

$app->post('/logout', function($req, $res, $args) use($app) {
    $Auth = new Auth($this);

    $data = $req->getParsedBody();

    if ($req->getAttribute('csrf_status') === false) {
        $this->logger->error('CSRF failure.');
        $this->flash->addMessage('danger', 'Internal error.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
    }

    $auth_token = $Auth->validateToken($_COOKIE['auth_token']);

    if (!$auth_token) {
        $this->logger->debug('Token object was not found.');
    }

    try {
        $Auth->destroySession($auth_token->id);

        $this->logger->info('User logged out.', ['user_id' => $auth_token->id]);
        $this->flash->addMessage('success', 'You have been logged out.');
    } catch (Exception $e) {
        $this->logger->error('User logout failed. '.$e->getMessage(), ['user_id' => $auth_token->id]);
        $this->flash->addMessage('danger', 'Log out attempt failed.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
    }

    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
})->setName('logout');
