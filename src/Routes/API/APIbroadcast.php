<?php

use \Carbon\Carbon;

$app->group('/API/broadcasts', function() use($app) {
    /***************************************************************************
     * GET 'broadcast'
     *
     * Broadcast history
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Broadcast = new Broadcast($this);

        $view['broadcasts'] = $Broadcast->getAll($req->getAttribute('user_id'));

        return $res->withJSON(['broadcasts'=>$view['broadcasts']]);
        //return $this->view->render($res, 'broadcast.html', $view);
    })->setName('broadcast');

    /***************************************************************************
     * POST 'broadcast'
     *
     * Validate broadcast message
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Broadcast = new Broadcast($this);
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if (!$data['subject'] || !$data['message'] || !$data['recipients']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJSON(['error'=>'The form was filled out incompletely.']);
        }

        $broadcast_id = $Broadcast->create($req->getAttribute('user_id'), $data);

        if ($data['recipients']['teachers']) {
            foreach ($School->getUsers($_SESSION['school_id']) as $user) {
                $users[$user->user_id] = $user;
            }
        }

        if ($data['recipients']['parents']) {
            foreach ($Child->getAll($_SESSION['school_id']) as $child) {
                foreach ($Child->getActiveParents($child->child_id) as $user) {
                    $users[$user->user_id] = $user;
                }
            }
        }

        foreach ($users as $user_id => $user) {
            if ($user_id == $req->getAttribute('user_id')) {
                continue;
            }

            $Broadcast->createUser($broadcast_id, $user_id);

            $this->mailer->send('broadcastMessage.html', [
                'to' => $user->user_email,
                'subject' => 'Preschool Broadcast: '.$data['subject'],
                'first_name' => $user->user_first_name,
                'user' => $req->getAttribute('user'),
                'message' => $data['message'],
                'school' => $School->getOne($_SESSION['school_id']),
            ]);

            $this->logger->info('Broadcast sent.', [ 'email' => $user->user_email ]);
        }

        $this->logger->info('Broadcast saved.', [ 'user_id' => $req->getAttribute('user_id') ]);

        return $res->withJSON(['success'=>'Your message has been broadcasted to the selected users.']);
        return $res->withStatus(302)->withRedirect($this->router->pathFor('broadcast'));
    });

    /***************************************************************************
     * GET 'broadcast/:broadcast_id'
     *
     * Broadcast details
     **************************************************************************/
    $this->get('/{ broadcast_id }', function($req, $res, $args) use($app) {
        $Broadcast = new Broadcast($this);

        $view['broadcast'] = $Broadcast->getOne($args['broadcast_id']);
        $view['recipients'] = $Broadcast->getUsers($args['broadcast_id']);

        if (!$view['broadcast']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        /**
         * Set read permission to false initially. Afterwards, we will iterate
         * through all the participants involved to check if the person
         * accessing this broadcast is eligible to view it. Otherwise return
         * with not found message.
         */
        $permission = false;

        if ($view['broadcast']->user_id == $req->getAttribute('user_id')) {
            $permission = true;
        } else {
            foreach ($view['recipients'] as $recipient) {
                if ($recipient->user_id == $req->getAttribute('user_id')) {
                    $permission = true;
                }
            }
        }

        if ($permission == false) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        return $res->withJSON(['broadcast'=>$view['broadcast'],'recipients'=>$view['recipients']]);
        return $this->view->render($res, 'broadcastDetails.html', $view);
    })->setName('broadcastDetails');
});
