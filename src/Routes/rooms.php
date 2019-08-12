<?php

$app->group('/rooms', function() use($app) {
    $this->post('/create', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        if (!$data['name'] || !$data['description']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        $Room->create($_SESSION['school_id'], $data);

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
    })->setName('roomCreate');

    $this->get('/{room_id}/edit', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Edit Room Details';

        $view['room'] = $Room->getOne($args['room_id']);

        if (!$view['room']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))->role != 1) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('roomEdit', ['room_id' => $args['room_id']]));
        }

        if ($view['room']->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        return $this->view->render($res, 'roomEdit.html', $view);
    })->setName('roomEdit');

    $this->post('/{room_id}/edit', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('roomEdit', ['room_id' => $args['room_id']]));
        }

        if ($Room->getOne($args['room_id'])->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('roomEdit', ['room_id' => $args['room_id']]));
        }

        $Room->setDetails($args['room_id'], $data);

        $this->logger->info('Room updated.', ['room_id' => $args['room_id'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Room details have been updated.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
    });

    $this->post('/{room_id}/delete', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if ($School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))->role != 1) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('roomEdit', ['room_id' => $args['room_id']]));
        }

        if ($Room->getOne($args['room_id'])->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('roomEdit', ['room_id' => $args['room_id']]));
        }

        if ($Room->getChildrenCount($args['room_id'])) {
            $this->logger->info('Room still has children.', ['room_id' => $args['room_id']]);
            $this->flash->addMessage('danger', 'Only empty rooms can be deleted.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('roomEdit', ['room_id' => $args['room_id']]));
        }

        $Room->purge($args['room_id']);

        $this->logger->info('Room deleted.', ['room_id' => $args['room_id'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Room has been deleted.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
    })->setName('roomDelete');
});
