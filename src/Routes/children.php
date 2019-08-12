<?php

use \Carbon\Carbon;

$app->group('/children', function() use($app) {
    /***************************************************************************
     * GET 'children'
     *
     * View all children of a school
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Child Profiles';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $view['school'] = $School->getOne($view['school_user']->school_id);

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (isset($_GET['search'])) {
            $view['children'] = $Child->getSearch($_SESSION['school_id'], $_GET['search']);
            $view['search'] = $_GET['search'];
        } else {
            $view['children'] = $Child->getAll($_SESSION['school_id']);
        }

        foreach($view['children'] as $position=>$value){
            $view['birthday'][$position]=$value->child_birthday;
            $monthBday[$position] = $view['birthday'][$position][5].$view['birthday'][$position][6];
            $dayBday[$position] = $view['birthday'][$position][8].$view['birthday'][$position][9];

            $todayMonth = date('m', time());
            $todayDay = date('d', time());

            if ($monthBday[$position] == $todayMonth){
                if($todayDay <=$dayBday[$position] ){
                   $view['daysDiff'][$position]=$dayBday[$position]-$todayDay;
                    switch ($view['daysDiff'][$position]){
                        case "7":
                            $view['daysBday'][$position]="In 7 days is my birthday!";
                            break;
                        /*case "6":
                            $view['daysBday'][$position]="In 6 days is my birthday!!";
                            break;
                        case "5":
                            $view['daysBday'][$position]="In 5 days is my birthday!!";
                            break;
                        case "4":
                            $view['daysBday'][$position]="In 4 days is my birthday!";
                            break;
                        case "3":
                            $view['daysBday'][$position]="In 3 days is my birthday!";
                            break;
                        case "2":
                            $view['daysBday'][$position]="In 2 days is my birthday!";
                            break;*/
                        case "1":
                            $view['daysBday'][$position]="Tomorrow is my birthday!";
                            break;
                        case "0":
                            $view['daysBday'][$position]="Today is my birthday!";
                            break;
                        }
                        $view['children'][$position]->daysDiff=$view['daysDiff'][$position];
                        $view['children'][$position]->daysBday=$view['daysBday'][$position];

                    }
                }else{
                    //$view['daysBday'][$position]="More than a week for your birthday  =(!";
                }
             }
          
        $view['archived_children'] = $Child->getAll($_SESSION['school_id'], 'D');
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        return $this->view->render($res, 'child.html', $view);
    })->setName('child');

    /***************************************************************************
     * POST 'children/create'
     *
     * Create a new child
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Timeline = new Timeline($this);

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

        if (!$data['room_id'] || !$data['name'] || !$data['gender'] || !$data['birthday']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data['birthday'])) {
            $this->logger->info('User submitted invalid birthday.');
            $this->flash->addMessage('danger', 'The birthday appears invalid. Please write as YYYY-MM-DD.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        $dt = Carbon::parse($data['birthday']);
        $data['birthday'] = $dt->toDateString();

        $child_id = $Child->create($_SESSION['school_id'], $data);

        $Timeline->create($req->getAttribute('user_id'), $child_id, 'create', $child_id, 1);

        $this->logger->info('Child profile created.', [ 'user_id' => $req->getAttribute('user_id'), 'child_id' => $child_id ]);
        $this->flash->addMessage('success', 'A new child profile has been created.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', [ 'child_id' => $child_id ]));
    })->setName('childCreate');

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
        $LearningSummary = new LearningsSummary($this);

        $view['flash'] = $this->flash->getMessages();
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
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        }

        $view['child'] = $Child->getOne($args['child_id']);
        $_SESSION['child_id'] = $args['child_id'];

        if (!$view['child']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            if ($view['child']->school_id != $_SESSION['school_id']) {
                $notFoundHandler = $this->get('notFoundHandler');

                return $notFoundHandler($req, $res);
            }
        }

        $view['title'] = $view['child']->child_name;
        $view['parents'] = $Child->getParents($args['child_id']);
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        /**
         * We will hide non-public timeline posts from parent's view by
         * specifically querying public = 1.
         */
        if ($req->getAttribute('user')->user_type == 'T') {
            $view['timelines'] = $Timeline->getAll($args['child_id'], 1);

            // Only show checklist count to teachers/admins
	        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);

        } else {
            $view['timelines'] = $Timeline->getAll($args['child_id'], 0);
        }

        foreach($view['timelines'] as $v) {
            if ($v->timeline_type == 'record') {
                $view['medias'][$v->timeline_id] = $Record->getMedias($v->timeline_linked_id);
            }

            if ($v->timeline_type == 'story') {
                $view['medias'][$v->timeline_id] = $Story->getMedias($v->timeline_linked_id);
            }

            if ($v->timeline_type == 'summary') {
                $view['medias'][$v->timeline_id] = $LearningSummary->getMedias($v->timeline_linked_id);
            }

            $view['comments'][$v->timeline_id] = $Timeline->getComments($v->timeline_id);
        }

        return $this->view->render($res, 'childDetails.html', $view);
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

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Daily Records';
        $view['navigation'] = 'records';
        $view['child'] = $Child->getOne($args['child_id']);

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
            $view['user_type'] = $req->getAttribute('user')->user_type;

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        }

        $view['types'] = [
            'note' => 'General Comment',
            'mood' => 'Disposition',
            'meal' => 'Meal',
            'nap' => 'Nap',
            'toilet' => 'Toilet',
            'diaper' => 'Diaper Change',
            'medication' => 'Medication',
        ];

        for ($i = 1; $i <= 52; $i++) {
            $view['weeks'][$i] = getStartAndEndDate($i, date('Y'));
        }

        $view['current_week'] = isset($_GET['week']) ? (int)$_GET['week'] : date('W');

        $records = $Record->getAllChild($args['child_id'], $view['current_week']);

        foreach ($records as $record) {
            $view['dates'][$record->record_date][$record->record_time][$record->record_id] = [
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

            if (isset($record_params)) {
                foreach ($record_params as $key => $value) {
                    $view['record_params'][$record->record_id][$key] = $value;
                }
            }
        }

        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        return $this->view->render($res, 'childRecord.html', $view);
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
        $Draft = new Draft($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Child Learning Stories';
        $view['navigation'] = 'stories';

        if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
            $view['user_type'] = $req->getAttribute('user')->user_type;

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }

            // get story drafts
		    $view['drafts'] = $Draft->getAllStories($args['child_id'], $req->getAttribute('user_id'));
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        }

        if ($req->getAttribute('user')->user_type == 'T') {
            $all = 1;
        } else {
            $all = 0;
        }

        $view['child'] = $Child->getOne($args['child_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);
        $view['stories'] = $Story->getAll($args['child_id'], $all);
        $view['story_count'] = count($view['stories']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        return $this->view->render($res, 'story.html', $view);
    })->setName('story');

    /***************************************************************************
     * GET 'children/:child_id/monthly/:year'
     *
     * View monthly plans associated to the child
     **************************************************************************/
    $this->get('/{child_id}/monthly/{year:[0-9]+}', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $Story = new Story($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Monthly Plans';
        $view['navigation'] = 'monthlyPlans';

        if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
        
        
        $view['months'] = array(
            1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December'
		);
        
        $child = $Child->getOne($args['child_id']);

        $monthly_plans = $Child->getAssociatedPlansForAYear($child->school_id, $child->child_id, $args['year'], 'monthly');

        $view['months_completed'] = PlanHelper::assembleMonthlyPlanStats($monthly_plans);
		$view['year'] = $args['year'];

        $view['child'] = $child;
        $view['children'] = $Child->getAll($child->school_id);
        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);

        return $this->view->render($res, 'childMonthlyYearSummary.html', $view);
    })->setName('childMonthlyYearSummary');

    /***************************************************************************
	 * GET 'children/:child_id/monthly/:month/:year'
	 *
	 * View plans associated to a specific month of the year
	 **************************************************************************/
	$this->get( '/{child_id}/monthly/{month:[0-9]+}/{year:[0-9]+}', function ( $req, $res, $args ) use ( $app ) {
        $Child = new Child($this);
        $Plan = new Plan($this);
        $Story = new Story($this);
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);

		$view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Monthly Plans';
        $view['navigation'] = 'monthlyPlans';

		$view['year'] = $args['year'];
		$view['month'] = $args['month'];
            
        if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $child = $Child->getOne($args['child_id']);

        $view['plans'] = $Child->getAssociatedMonthlyPlansForAMonth($child->school_id, $child->child_id, $args['year'], $args['month']);

		// Grab associations
		foreach($view['plans'] as $key => $plan) {

			$view['plans'][$key]->assocs = $Plan->getAllMonthlyPlanAssociations(
				PlanHelper::assocEntityToDbTable($plan->assoc),
				$plan->monthly_plan_id,
				$plan->assoc);
        }

        $view['child'] = $child;
        $view['children'] = $Child->getAll($child->school_id);
        $view['story_count'] = $Story->getChildCount($args['child_id']);
        $view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
        $view['accident_count'] = $Accident->getChildCount($args['child_id']);
        

		return $this->view->render( $res, 'childMonthlyPlanSummary.html', $view );
    })->setName( 'childMonthlyPlanSummary' );

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
            $view['user_type'] = $req->getAttribute('user')->user_type;

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        } else {
			// We no longer want to show parents Observations Checklists
	        $this->logger->notice('School::ParentAccessingChecklist.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
	        $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

	        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
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
            $view['user_type'] = $req->getAttribute('user')->user_type;

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        } else {
            if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
                $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
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

        return $this->view->render($res, 'accident.html', $view);
    })->setName('accident');

    /***************************************************************************
     * POST 'children/:child_id/edit'
     *
     * Validate child edit form
     **************************************************************************/
    $this->post('/{child_id}/edit', function($req, $res, $args) use($app) {
        $Child = new Child($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$data['room_id'] || !$data['name'] || !$data['gender'] || !$data['birthday']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        if (!$Child->setDetails($args['child_id'], $data)) {
            $this->logger->notice('Child::setDetails failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'Child profile could not be saved.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childEdit', ['child_id' => $args['child_id']]));
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

        $this->flash->addMessage('success', 'Child profile was updated successfully.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
    })->setName('childEdit');

    /***************************************************************************
     * POST 'children/:child_id/notes'
     *
     * Validate child notes form
     **************************************************************************/
    $this->post('/{child_id}/notes', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        /**
         * Both teachers and parents should be allowed to update a child's
         * notes without further approval.
         */
        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id')) && !$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $Child->setNotes($args['child_id'], $data);

        $this->logger->info('Notes saved.', ['child_id' => $args['child_id'], 'user_id' => $invited_user->user_id]);
        $this->flash->addMessage('success', 'The notes were successfully saved.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('childDetails', ['child_id' => $args['child_id']]));
    })->setName('childEditNotes');

    /***************************************************************************
     * POST 'children/:child_id/archive'
     *
     * Validate child archiving form
     **************************************************************************/
    $this->post('/{child_id}/archive', function($req, $res, $args) use($app) {
        $Child = new Child($this);
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

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        /**
         * Archived children will continue to be charged because logically they
         * still consume disk space and allow users to view its details.
         */
        $Child->setStatus($args['child_id'], 'D');

        $this->logger->info('Child archived.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'The child profile has been archived.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
    })->setName('childArchive');

    /***************************************************************************
     * POST 'children/:child_id/delete'
     *
     * Validate child deletion form
     **************************************************************************/
    $this->post('/{child_id}/delete', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
        }

        if ($Child->purge($args['child_id'])) {
            // Also delete all observations, pictures and files
        }

        $this->logger->info('Child deleted.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'The child profile has been deleted.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('child'));
    })->setName('childDelete');
});
