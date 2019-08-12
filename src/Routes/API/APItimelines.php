<?php

$app->group('/API/timelines', function() use($app) {
    /***************************************************************************
     * POST 'timelines/comment'
     *
     * Validate timeline comment form
     **************************************************************************/
    $this->post('/comment', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Timeline = new Timeline($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id')) && !$Child->getParent($data['child_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (!$data['comment'] || !$data['timeline_id']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        $Timeline->createComment($data['timeline_id'], $req->getAttribute('user_id'), $data);

        /**
         * Fetch the timeline poster and cache in $users, so that he definitely
         * gets a notification.
         */
        $timeline = $Timeline->getOne($data['timeline_id']);

        $users[$timeline->user_id] = $User->getOne($timeline->user_id);

        /**
         * Now all the users who have once contributed to the timeline thread.
         */
        foreach ($Timeline->getComments($data['timeline_id']) as $comment) {
            $users[$comment->user_id] = $User->getOne($comment->user_id);
        }

        $child = $Child->getOne($data['child_id']);

        foreach ($users as $user_id => $user) {
            if ($user_id == $req->getAttribute('user_id')) {
                continue;
            }

            /**
             * Do not send a notification to parents if the timeline post is
             * supposed to be private.
             */
            if ($req->getAttribute('user_id')->user_type == 'P' && $timeline->timeline_public == '0') {
                continue;
            }

            if (!$user->user_notify_comment) {
                continue;
            }

            $this->mailer->send('commentNotify.html', [
                'to' => $user->user_email,
                'subject' => 'A new timeline comment has been posted for you',
                'first_name' => $user->user_first_name,
                'user' => $req->getAttribute('user'),
                'comment' => $data['comment'],
                'child' => $child,
            ]);

            $this->logger->info('Notification sent.', [ 'email' => $user->user_email ]);
        }

        $this->logger->info('Comment created.', [ 'user_id' => $req->getAttribute('user_id') ]);

        return $res->withJson(['success'=>'Your comment has been saved in the timeline.']);
    })->setName('timelineComment');
});
