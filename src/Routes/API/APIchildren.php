<?php

$app->group('/API/children', function() use($app) {

    /***************************************************************************
     * GET 'children'
     *
     * View all children of a school
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        
        if (isset($_GET['search'])) {
            $view['children'] = $Child->getSearch($_SESSION['school_id'], $_GET['search']);
            $view['search'] = $_GET['search'];
        } else {
            $children = $Child->getAll($_SESSION['school_id']);
        }

        $archived_children = $Child->getAll($_SESSION['school_id'], 'D');
        $rooms= $Room->getAll($_SESSION['school_id']);

        $data=array('childs'=>$children,'rooms'=>$rooms,'archived_child'=>$archived_children);

        return $res->withJson($data);
    })->setName('child');

    /***************************************************************************
     * GET 'children/:child_id'
     *
     * View child item
     **************************************************************************/
    $this->get('/{child_id}', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $Record = new Record($this);
        $Room = new Room($this);
        $School = new School($this);
        $Story = new Story($this);
        $Timeline = new Timeline($this);

        $view['navigation'] = 'timeline';
        $user_type = '';
        if ($_COOKIE['auth_token']) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
            $user_type = $token->user_type;

        }
        $view['user_type'] = $user_type;
        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
            $view['school'] = $School->getOne($view['school_user']->school_id);

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);

                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        }

        $child = $Child->getOne($args['child_id']);

        if (!$child) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $res->withJson(['error'=>'No child found.']);
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            if ($child->school_id != $_SESSION['school_id']) {
                $notFoundHandler = $this->get('notFoundHandler');

                return $res->withJson(['error'=>'No child found.']);
            }
        }

        $parents= $Child->getParents($args['child_id']);
        $rooms= $Room->getAll($_SESSION['school_id']); //Edit child
        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        /**
         * We will hide non-public timeline posts from parent's view by
         * specifically querying public = 1.
         */
        if ($req->getAttribute('user')->user_type == 'T') {
            $timelines = $Timeline->getAll($args['child_id'], 1);

            // Only show checklist count to teachers/admins
	        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);

        } else {
            $timelines = $Timeline->getAll($args['child_id'], 0);
        }

        foreach($timelines as $v) {
            if ($v->timeline_type == 'record') {
                $record[$v->timeline_id] = $Record->getMedias($v->timeline_linked_id);
            }

            if ($v->timeline_type == 'story') {
                $story[$v->timeline_id] = $Story->getMedias($v->timeline_linked_id);
            }

            $comments[$v->timeline_id] = $Timeline->getComments($v->timeline_id);
        }

        $data=array('child'=>$child,'parents'=>$parents,'rooms'=>$rooms,'timeline'=>$timelines,'comment'=>$comments,'story'=>$story,'record'=>$record);

        return $res->withJson($data);
        
    })->setName('childDetails');

    /***************************************************************************
     * GET 'children/:child_id/records'
     *
     * View records of a child
     **************************************************************************/
    $this->get('/{child_id}/records', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $Record = new Record($this);
        $School = new School($this);
        $Story = new Story($this);
        $User = new User($this);

        $view['navigation'] = 'records';
        $view['child'] = $Child->getOne($args['child_id']);

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);


                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);

                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        }

        $types = [
            'note' => 'General Comment',
            'mood' => 'Disposition',
            'meal' => 'Meal',
            'nap' => 'Nap',
            'toilet' => 'Toilet',
            'diaper' => 'Diaper Change',
            'medication' => 'Medication',
        ];

        for ($i = 1; $i <= 52; $i++) {
            $weeks[$i] = getStartAndEndDate($i, date('Y'));
        }

        $view['current_week'] = $_GET['week'] ? (int)$_GET['week'] : date('W');

        $records = $Record->getAllChild($args['child_id'], $view['current_week']);

        foreach ($records as $record) {
            $dates[$record->record_date][$record->record_time][$record->record_id] = [
                'record_public' => $record->record_public,
                'record_date' => $record->record_date,
                'record_time' => $record->record_time,
                'record_type' => $record->record_type,
                'record_comment' => $record->record_comment,
                'record_medias' => $Record->getMedias($record->record_id),
                'record_user' => $User->getOne($record->user_id),
                'created_at' => $record->created_at,
            ];

            foreach ($Record->getParams($record->record_id) as $param) {
                $record_params[$param->param_id] = $param->param_value;
            }

            if ($record_params) {
                foreach ($record_params as $key => $value) {
                    $records_params[$record->record_id][$key] = $value;
                }
            }
        }

        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        $data=array('records'=>$records,'records_param'=>$records_params,'types'=>$types,'weeks'=>$weeks,'dates'=>$dates);

        return $res->withJson($data);
    })->setName('childRecord');

    /***************************************************************************
     * GET 'children/:child_id/stories' 
     *
     * View learning stories of a child
     **************************************************************************/
    $this->get('/{child_id}/stories', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);

        $view['navigation'] = 'stories';

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            $all = 1;
        } else {
            $all = 0;
        }

        $view['child'] = $Child->getOne($args['child_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);
        $stories= $Story->getAll($args['child_id'], $all);
        $view['story_count'] = count($stories);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        return $res->withJson($stories);
    })->setName('story');

    /***************************************************************************
     * GET 'children/:child_id/checklists'
     *
     * View checklists of a child
     **************************************************************************/
    $this->get('/{child_id}/checklists', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Add Milestone Observations';
        $view['navigation'] = 'checklists';
        $view['child'] = $Child->getOne($args['child_id']);

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
			// We no longer want to show parents Observations Checklists
	        $this->logger->notice('School::ParentAccessingChecklist.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $month = StringHandler::convertBirthdayToMonthCount($view['child']->child_birthday);
        $view['checklists'] = $Checklist->getAll($args['child_id'], $month);

        if (isset($_GET['checklist'])) {
            $checklist_id = $_GET['checklist'];
        } else {
            $checklist_id = $view['checklists'][0]->checklist_id;
        }

        $view['current_checklist_id'] = $checklist_id;

        /**
         * We will now go through the individual checklist items and categorize
         * them into category blocks. By storing the array this way, we can loop
         * through the categories first, followed by the individual items.
         */
        foreach ($Checklist->getCategories() as $category) {
            $view['categories'][$category->category_name] = $Checklist->getAllMilestones($checklist_id, $category->category_id);
        }

        foreach ($Checklist->getObservations($args['child_id']) as $observation) {
            $view['observations'][$observation->token_id][$observation->milestone_id] = $observation;
            $view['users'][$observation->token_id] = [
                'user_first_name' => $observation->user_first_name,
                'user_last_name' => $observation->user_last_name,
                'user_avatar_url' => $observation->user_avatar_url,
                'checklist_created_at' => $observation->checklist_created_at,
            ];
        }

        /**
         * Although the red flags will be retrieved the same way, we won't have
         * categories, so it is simply stored in the view directory without a
         * multi-dimensional array.
         */
        $view['red_flags'] = $Checklist->getRedFlags($month);

        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        return $this->view->render($res, 'checklist.html', $view);
    })->setName('checklist');


    /***************************************************************************
     * GET 'children/:child_id/accidents'
     *
     * View accidents of a child
     **************************************************************************/
    $this->get('/{child_id}/accidents', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $School = new School($this);
        $Story = new Story($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Accidents and Incidents';
        $view['navigation'] = 'accidents';
        $view['child'] = $Child->getOne($args['child_id']);

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                
                return $res->withJson(['error'=>'You don’t have sufficient rights.']);
            }
        }

        $view['accidents'] = $Accident->getAll($_SESSION['school_id'], $args['child_id']);

        /**
         * Agreed with Wendy to display accidents of the last 2 years.
         */
        for ($i = 24; $i >= 0; $i--) {
            $time = strtotime("-$i month");

            $current_year = date('Y', $time);
            $current_month = date('m', $time);

            $view['months'][] = [
                'label' => $current_year.'-'.$current_month,
                'count' => $Accident->getMonthCount($_SESSION['school_id'], $current_year, $current_month),
            ];
        }

        $view['children'] = $Child->getAll($_SESSION['school_id']);
        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = count($view['accidents']);

        return $res->withJson($view['accidents']);
    })->setName('accident');

    /***************************************************************************
     * POST 'children/create'
     *
     * Create a new child
     * 
     * param:  room_id, name, gender, birthday,  option:street, city, postal_code, phone
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Timeline = new Timeline($this);

        $data = $req->getParsedBody();

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (!$data['room_id'] || !$data['name'] || !$data['gender'] || !$data['birthday']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data['birthday'])) {
            $this->logger->info('User submitted invalid birthday.');

            return $res->withJson(['error'=>'The birthday appears invalid. Please write as YYYY-MM-DD.']);
        }

        $dt = Carbon::parse($data['birthday']);
        $data['birthday'] = $dt->toDateString();

        $child_id = $Child->create($_SESSION['school_id'], $data);

        $Timeline->create($req->getAttribute('user_id'), $child_id, 'create', $child_id, 1);

        $this->logger->info('Child profile created.', [ 'user_id' => $req->getAttribute('user_id'), 'child_id' => $child_id ]);

        return $res->withJson(['success'=>'Child profile created.']);
    })->setName('childCreate');

    /***************************************************************************
     * POST 'children/:child_id/edit'
     *
     * Validate child edit form
     * 
     * param: room_id, name, gender, birthday
     **************************************************************************/
    $this->post('/{child_id}/edit', function($req, $res, $args) use($app) {
        $Child = new Child($this);

        $data = $req->getParsedBody();

        if (!$data['room_id'] || !$data['name'] || !$data['gender'] || !$data['birthday']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        if (!$Child->setDetails($args['child_id'], $data)) {
            $this->logger->notice('Child::setDetails failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'Child profile could not be saved.']);
        }

        if ($data['avatar']) {
            $child = $Child->getOne($args['child_id']);

            $this->logger->debug('Attempting upload.', [ 'avatar' => $data['avatar'] ]);

            $avatar = $this->uploader->getFile($data['avatar']);

            if ($avatar->data['is_image']) {
                if ($child->child_avatar_url) {
                    $this->logger->debug('Attempt to delete old avatar.', [ 'url' => $child->child_avatar_url ]);

                    $old_avatar = $this->uploader->getFile($child->child_avatar_url);

                    if ($old_avatar->filename) {
                        $old_avatar->delete();
                    }
                }

                $resized_avatar = $this->uploader->createLocalCopy($avatar->getUrl());
                $resized_avatar->store();

                $avatar->delete();

                $Child->setAvatar($args['child_id'], $resized_avatar->getUrl());
            }
        }

        return $res->withJson(['success'=>'Child profile was updated successfully.']);
    })->setName('childEdit');

    /***************************************************************************
     * POST 'children/:child_id/notes'
     *
     * Validate child notes form
     * 
     * param: notes
     **************************************************************************/
    $this->post('/{child_id}/notes', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        /**
         * Both teachers and parents should be allowed to update a child's
         * notes without further approval.
         */
        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id')) && !$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $Child->setNotes($args['child_id'], $data);

        $this->logger->info('Notes saved.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);

        return $res->withJson(['success'=>'The notes were successfully saved.']);
    })->setName('childEditNotes');
});
