<?php

$app->group('/API/stories', function() use($app) {
    /***************************************************************************
     * GET 'stories/create'
     *
     * View create stories
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (isset($_GET['child_id'])) {
            $view['child_id'] = $_GET['child_id'];
            $view['child'] = $Child->getOne($_GET['child_id']);

            if ($view['child']->child_status != 'A') {
                $this->logger->notice('Create story failed.', ['child_id' => $_GET['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            $view['children'] = $Child->getAll($_SESSION['school_id']);
        }

        $school = $School->getOne($_SESSION['school_id']);
        if ($view['child']->child_birthday) {
            $categories = $Story->getCategoriesByMonth($school->country_id, StringHandler::convertBirthdayToMonthCount($view['child']->child_birthday));
        } else {
            $categories = $Story->getCategories($school->country_id);
        }

        if ($categories) {
            foreach ($categories as $category) {
                $view['categories'][$category->category_id] = [
                    'category_name' => $category->category_name,
                    'category_description' => $category->category_description,
                    'category_group' => $category->category_group,
                    'framework_id' => $category->framework_id,
                    'framework_name' => $category->framework_name,
                    'framework_month_min' => $category->framework_month_min,
                    'framework_month_max' => $category->framework_month_max,
                    'goals' => $Story->getGoals($category->category_id),
                    'texts' => $Story->getTexts($category->category_id)
                ];

                $view['frameworks'][$category->framework_id] = [
                    'framework_name' => $category->framework_name,
                    'framework_month_min' => $category->framework_month_min,
                    'framework_month_max' => $category->framework_month_max,
                ];

                $groups[$category->framework_id][] = $category->category_group;
            }

            foreach ($groups as $framework_id => $group) {
                $view['groups'][$framework_id] = array_unique($groups[$framework_id]);
            }
        }

        $data=array('categorie'=>$view['categories'],'frameworks'=>$view['frameworks'],'groups'=>$view['groups']);

        return $res->withJSON($data);

        return $this->view->render($res, 'storyCreate.html', $view);
    })->setName('storyCreate');

    /***************************************************************************
     * POST 'stories/create'
     *
     * Save create stories
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Media = new Media($this);
        $Story = new Story($this);
        $Timeline = new Timeline($this);

        $data = $req->getParsedBody();

        if ($Child->getOne($data['child_id'])->school_id != $_SESSION['school_id']) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            v(['error'=>'You don’t have sufficient rights.']);
        }

        $data['public'] = $data['public'] ? 1 : 0;
        $story_id = $Story->create($req->getAttribute('user_id'), $data['child_id'], $data);

        if (!$story_id) {
            $this->logger->warning('Create story failed.');

            return $res->withJson(['error'=>'The story could not be created.']);
        }

        if ($data['goals']) {
            foreach ($data['goals'] as $v) {
                $Story->createGoal($v, $story_id);
            }
        }

        if ($data['texts']) {
            foreach ($data['texts'] as $text_id => $text) {
                $Story->createText($text, $text_id, $story_id);
            }
        }

        if ($data['media']) {
            $this->logger->debug('Media files found.', [ 'group' => $data['media'] ]);

            $group = $this->uploader->getGroup($data['media']);
            $files = $group->getFiles();

            foreach ($files as $file) {
                $url_full = $file->resize(1600)->getUrl();
                $url_thumbnail = $file->resize(400)->getUrl();

                $resized_full = $this->uploader->createLocalCopy($url_full);
                $resized_full->store();

                $resized_thumbnail = $this->uploader->createLocalCopy($url_thumbnail);
                $resized_thumbnail->store();

                $file->delete();

                $this->logger->debug('Saved media.', [ 'story_id' => $args['story_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail->getUrl() ]);

                $media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail->getUrl(), $resized_full->data['mime_type']);
                $Story->createMedia($media_id, $story_id);
            }
        }

        $public = $data['public'] ? 1 : 0;

        $Timeline->create($req->getAttribute('user_id'), $data['child_id'], 'story', $story_id, $public, $data['comment']);

        if ($public) {
            $child = $Child->getOne($data['child_id']);

            foreach ($Child->getParents($data['child_id']) as $parent) {
                if (!$parent->user_notify_story) {
                    continue;
                }

                $this->mailer->send('storyNotify.html', [
                    'to' => $parent->user_email,
                    'subject' => 'A new learning story has been created for your child',
                    'first_name' => $parent->user_first_name,
                    'user' => $req->getAttribute('user'),
                    'story_id' => $story_id,
                    'child' => $child,
                ]);

                $this->logger->info('Notification sent.', [ 'email' => $parent->user_email ]);
            }
        }

        $this->logger->info('Story saved.', ['story_id' => $story_id, 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'The learning stories have been saved.');
        return $res->withJson(['success'=>'The learning stories have been saved.']);
        //return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('storyDetails', [story_id => $story_id]));
    });

    /***************************************************************************
     * GET 'stories/:story_id'
     *
     * View stories  form
     **************************************************************************/
    $this->get('/{story_id}', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);

        $view['story'] = $Story->getOne($args['story_id']);

        if (!$view['story']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            $child_user = $Child->getParent($view['story']->child_id, $req->getAttribute('user_id'));

            if (!$child_user) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }

            if ($view['story']->story_public == 0) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        }

        $child = $Child->getOne($view['story']->child_id);

        if ($view['school_user']->school_id != $child->school_id && $child_user->child_id != $child->child_id) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $view['goals'] = $Story->getStoryGoals($args['story_id']);
        $view['texts'] = $Story->getStoryTexts($args['story_id']);
        $view['medias'] = $Story->getMedias($args['story_id']);

        foreach ($view['goals'] as $goals) {
            $view['frameworks'][$goals->framework_name][$goals->category_group][$goals->category_name]['goals'] = $view['goals'];
        }

        foreach ($view['texts'] as $text) {
            $view['frameworks'][$text->framework_name][$text->category_group][$text->category_name]['texts'] = $view['texts'];
        }

        $data=array('story'=>$view['story'],'frameworks'=>$view['frameworks'],'goals'=>$view['goals'],'texts'=>$view['texts']);
        return $res->withJson($data);
        //return $this->view->render($res, 'storyDetails.html', $view);
    })->setName('storyDetails');

    /***************************************************************************
     * GET 'stories/:story_id/edit'
     *
     * View stories edit form
     **************************************************************************/
    $this->get('/{story_id}/edit', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);

        $view['story'] = $Story->getOne($args['story_id']);

        if ($view['story']->user_id != $req->getAttribute('user_id')) {
            $this->logger->notice('Story::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $view['child'] = $Child->getOne($view['story']->child_id);

        $view['formdata'] = $view['story'];
        $goals = $Story->getStoryGoals($args['story_id']);
        $texts = $Story->getStoryTexts($args['story_id']);

        foreach ($goals as $goal) {
            $view['formgoals'][] = $goal->goal_id;
        }
        foreach ($texts as $text) {
            $view['formtexts'][$text->text_id] = $text->contents;
        }

        $school = $School->getOne($_SESSION['school_id']);
        $categories = $Story->getCategories($school->country_id);

        if ($categories) {
            foreach ($categories as $category) {
                $view['categories'][$category->category_id] = [
                    'category_name' => $category->category_name,
                    'category_description' => $category->category_description,
                    'category_group' => $category->category_group,
                    'framework_id' => $category->framework_id,
                    'framework_name' => $category->framework_name,
                    'framework_month_min' => $category->framework_month_min,
                    'framework_month_max' => $category->framework_month_max,
                    'goals' => $Story->getGoals($category->category_id),
                    'texts' => $Story->getTexts($category->category_id)
                ];

                $view['frameworks'][$category->framework_id] = [
                    'framework_name' => $category->framework_name,
                    'framework_month_min' => $category->framework_month_min,
                    'framework_month_max' => $category->framework_month_max,
                ];

                $groups[$category->framework_id][] = $category->category_group;
            }

            foreach ($groups as $framework_id => $group) {
                $view['groups'][$framework_id] = array_unique($groups[$framework_id]);
            }
        }

        $data=array('categorie'=>$view['categories'],'frameworks'=>$view['frameworks'],'groups'=>$view['groups'],
            'story'=>$view['story'],'formgoals'=>$view['formgoals'],'formtexts'=>$view['formtexts']);

        return $res->withJSON($data);

        return $this->view->render($res, 'storyCreate.html', $view);
    })->setName('storyEdit');

    /***************************************************************************
     * POST 'stories/:story_id/edit'
     *
     * Save stories edit
     **************************************************************************/
    $this->post('/{story_id}/edit', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Story = new Story($this);

        $data = $req->getParsedBody();

        $story = $Story->getOne($args['story_id']);

        if ($story->user_id != $req->getAttribute('user_id')) {
            $this->logger->notice('Story::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $Story->setDetails($args['story_id'], $data);
        $Story->purgeGoals($args['story_id']);
        $Story->purgeTexts($args['story_id']);

        if ($data['goals']) {
            foreach ($data['goals'] as $v) {
                $Story->createGoal($v, $args['story_id']);
            }
        }

        if ($data['texts']) {
            foreach ($data['texts'] as $text_id => $text) {
                $Story->createText($text, $text_id, $args['story_id']);
            }
        }

        if ($data['media']) {
            $this->logger->debug('Media files found.', [ 'group' => $data['media'] ]);

            $group = $this->uploader->getGroup($data['media']);
            $files = $group->getFiles();

            foreach ($files as $file) {
                $url_full = $file->resize(1600)->getUrl();
                $url_thumbnail = $file->resize(400)->getUrl();

                $resized_full = $this->uploader->createLocalCopy($url_full);
                $resized_full->store();

                $resized_thumbnail = $this->uploader->createLocalCopy($url_thumbnail);
                $resized_thumbnail->store();

                $file->delete();

                $this->logger->debug('Saved media.', [ 'story_id' => $args['story_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail->getUrl() ]);

                $media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail->getUrl(), $resized_full->data['mime_type']);
                $Story->createMedia($media_id, $args['story_id']);
            }
        }

        $this->logger->info('Story saved.', ['story_id' => $args['story_id'], 'user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'=>'The learning stories have been saved.']);
        //return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('storyDetails', [story_id => $args['story_id']]));
    });

    /***************************************************************************
     * POST 'stories/:story_id/delete'
     *
     * Delete stories
     **************************************************************************/
    $this->post('/{story_id}/delete', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);
        $Timeline = new Timeline($this);

        $story = $Story->getOne($args['story_id']);
        $child_id = $story->child_id;

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($view['school_user'] != 1 && $req->getAttribute('user_id') != $story->user_id) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if ($Story->purge($args['story_id'])) {
            $Timeline->purge('story', $args['story_id']);

            foreach($Story->getMedias($args['story_id']) as $media) {
                $Story->purgeGoals($args['story_id']);
                $Story->purgeMedia($media->media_id);
                // $Story->purgeMediaStory($media->id, $args['story_id']);

                $file = $this->uploader->getFile($media->media_full_url);
                $file->delete();
            }
        } else {
            $this->logger->error('Story deletion fail.');

            return $res->withJson(['error'=>'The story could not be deleted.']);
        }

        $this->logger->info('Story deleted.', ['story_id' => $args['story_id'], 'user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'=>'The learning stories have been deleted.']);
        //return $res->withStatus(302)->withRedirect($this->router->pathFor('story', [ 'child_id' => $child_id ]));
    })->setName('storyDelete');
});
