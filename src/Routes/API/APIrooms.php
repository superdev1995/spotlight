<?php

$app->group('/API/rooms', function() use($app) {

    /***************************************************************************
     * POST ' create'
     *
     * Create a new room 
     * 
     * Param: name, description
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (!$data['name'] || !$data['description']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        $Room->create($_SESSION['school_id'], $data);

        return $res->withJson(['success'=>'Room has been created.']);
    })->setName('roomCreate');

    /***************************************************************************
     * POST ' edit'
     *
     * Edit a new room 
     * 
     * Param: name, description
     **************************************************************************/

    $this->post('/{room_id}/edit', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if ($Room->getOne($args['room_id'])->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $Room->setDetails($args['room_id'], $data);

        $this->logger->info('Room updated.', ['room_id' => $args['room_id'], 'user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'=>'Room details have been updated.']);
    });

    /***************************************************************************
     * POST ' delete'
     *
     * Delete a new room 
     * 
     **************************************************************************/
    $this->post('/{room_id}/delete', function($req, $res, $args) use($app) {
        $Room = new Room($this);
        $School = new School($this);

        if ($School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))->role != 1) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if ($Room->getOne($args['room_id'])->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Room invalid.', ['school_id' => $view['room']->school_id, 'room_id' => $args['room_id']]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if ($Room->getChildrenCount($args['room_id'])) {
            $this->logger->info('Room still has children.', ['room_id' => $args['room_id']]);

            return $res->withJson(['error'=>'Only empty rooms can be deleted.']);
        }

        $Room->purge($args['room_id']);

        $this->logger->info('Room deleted.', ['room_id' => $args['room_id'], 'user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'=>'Room has been deleted.']);
    })->setName('roomDelete');
});
