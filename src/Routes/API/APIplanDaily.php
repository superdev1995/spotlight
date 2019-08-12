<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

$app->group('/API/plans', function() use($app) {
	/***************************************************************************
	 * GET 'daily/'
	 *
	 * View summary page where user can select date to display specific Daily
	 *  Plan
	 **************************************************************************/
	$this->get( '/daily', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Daily Plans';

		$Plan = new Plan($this);

		/**
		 * We have a date that was submitted
		 * - Show Daily Plans for this date
		 */
		if(!empty($req->getQueryParam('dailyPlan_date'))) {
			$view['request_date'] = $req->getQueryParam('dailyPlan_date');
			$view['plans'] = $Plan->getAllDailyPlans($_SESSION['school_id'], $req->getQueryParam('dailyPlan_date'));
		}
		else
		{
			$view['request_date'] = date("Y-m-d");
			$view['plans'] = $Plan->getAllDailyPlans($_SESSION['school_id'], date("Y-m-d"));
		}

		return $res->withJson($view['plans']);
	})->setName( 'summaryDailyPlan' );


	/***************************************************************************
	 * GET 'dailyPlan/create'
	 *
	 * Load a view where user can create new Daily Plan
	 **************************************************************************/
	$this->get( '/daily/create', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Create Daily Plan';

		$Child = new Child($this);
		$Room = new Room($this);

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];

		$School = new School($this);
		$Story = new Story($this);

		$school = $School->getOne($_SESSION['school_id']);

		$categories = $Story->getCategories($school->country_id);

		if ($categories) {	//TODO ADD CATEGORIES
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
				];

				$view['frameworks'][(int)$category->framework_id] = [
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

		//echo '<pre>'; print_r($categories); echo '</pre>';

		//$data=array('child'=>$Child,'room'=>$Room);
		$data=array('children'=>$view['children'],'rooms'=>$view['rooms']);
		//return $res->withJson($view['children']);

		return $res->withJson($data);
	})->setName( 'createDailyPlan' );


	/***************************************************************************
	 * POST 'dailyPlan/create'
	 *
	 * Save data for a new Daily Plan
	 **************************************************************************/
	$this->post('/daily/create', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);

		$req_body = $req->getParsedBody();

		$data = $req->getParsedBody();

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		$this->flash->addMessage('formdata', $data);
        
        if ($data['plan_img']) {
            $plan_img = $this->uploader->getFile($data['plan_img']);
            
            if ($plan_img->data['is_image']) {
                
                $plan_img_url=$plan_img->getUrl();
            }
        }
        
        // Create daily plan entry in the DB
		$plan_id = $Plan->addDailyPlan( $_SESSION['school_id'],
			$req_body['record_date'],
			$req_body['dailyPlan_name'],
			$req_body['dailyPlan_type'],
			$req->getAttribute('user_id'),
            $plan_img_url);

		if ($data['goals']) {
			foreach ($data['goals'] as $goal_id) {
				$Plan->createGoalDailyPlan($goal_id, $plan_id);
			}
		}
        
        /**
		 * Add plan blocks
		 */
		$blocks = array();

		for($i = 0; $i < count($req_body['time-block']); $i++) {
			// Make sure that we're adding blocks with content in them
			if($req_body['time-block'][$i] != null || $req_body['description'][$i] != null) {
				array_push($blocks, array(
					'time_block' => $req_body['time-block'][$i],
					'description' => $req_body['description'][$i]
				));
			}
		}

		$Plan->addDailyPlanBlocks($plan_id, $blocks);

		/**
		 * Associate plan with school / room / children
		 * TODO: Optimize, as this is used in multiple places
		 */
		$assoc_arr = array();

		if($req_body['dailyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $req_body[$req_body['dailyPlan_type']];
		}
        
		$Plan->associateDailyPlan($plan_id, $req_body['dailyPlan_type'], $assoc_arr);

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryDailyPlan'));
	});


	/***************************************************************************
	 * POST 'dailyPlan/delete'
	 *
	 * Delete Daily Plan
	 **************************************************************************/
	$this->post('/daily/delete', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);

		$data = $req->getParsedBody();

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		$this->flash->addMessage('formdata', $data);

		$req_body = $req->getParsedBody();

		$Plan->purgeDailyPlanGoals($args['daily_plan_id']);
		$Plan->deleteDailyPlan($req_body['daily_plan_id']);
		$Plan->deleteDailyPlanBlocks($req_body['daily_plan_id']);

		$this->flash->addMessage('success', 'Daily plan has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryDailyPlan'));
	})->setName( 'deleteDailyPlan' );


	/***************************************************************************
	 * POST 'dailyPlan/{daily_plan_id}/edit'
	 *
	 * Load the page and data for a specific Daily Plan for editing
	 **************************************************************************/
	$this->get('/daily/{daily_plan_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$Child = new Child($this);
		$Room = new Room($this);
		$AssociationGenerator = new AssociationGenerator($this, $args['daily_plan_id'], "daily");

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Edit Daily Plan';

		$view['daily_plan'] = $Plan->getPlan($args['daily_plan_id']);

		if ($view['daily_plan']->school_fk != $_SESSION['school_id']) {
			$this->logger->notice('daily Plan::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('summaryDailyPlan'));
		}

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);
		$view['formdata'] = $view['daily_plan'];
		$view['blocks'] = $Plan->getPlanBlocks($args['daily_plan_id']);
		$view['associations'] = $AssociationGenerator->getAssociations();

		/**
		 * TODO: refactor
		 */
		$assoc_id_arr = array();
		foreach($view['associations'] as $assoc){
			array_push($assoc_id_arr, $assoc->id);
		}

		$view['assoc_id_arr'] = $assoc_id_arr;

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];

		$School = new School($this);
		$Story = new Story($this);

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
				];

				$view['frameworks'][(int)$category->framework_id] = [
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

		$goals = $Plan->getDailyPlanGoals($args['daily_plan_id']);

		foreach ($goals as $goal) {
			$view['formgoals'][] = $goal->goal_id;
		}

		return $this->view->render($res, 'dailyPlanCreate.html', $view);
	})->setName( 'editDailyPlan' );


	/***************************************************************************
	 * POST 'dailyPlan/{daily_plan_id}/edit'
	 *
	 * Save data for an edited Daily Plan
	 **************************************************************************/
	$this->post('/daily/{daily_plan_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$PlanAssociations = new PlanAssociations($this);
		$School = new School($this);
        
        $plan_img_url = $Plan->getPlan($args['daily_plan_id'])->plan_img_url;
        
		$req_body = $req->getParsedBody();

		$data = $req->getParsedBody();

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}
        
		$this->flash->addMessage('formdata', $data);

		$plan_id = $args['daily_plan_id'];
        
        if ($data['plan_img']) {
            $plan_img = $this->uploader->getFile($data['plan_img']);
            
            if ($plan_img->data['is_image']) {
                
                if ($plan_img_url.plan_img_url) {
                    $this->logger->debug('Attempt to delete old image.', [ 'url' => $plan_img_url.plan_img_url]);
                    
                    $old_img = $this->uploader->getFile($plan_img_url.plan_img_url);
                    $old_img->delete();
                }
                
                $plan_img_url=$plan_img->getUrl();
            }
        }
        
		// Edit daily plan entry in the DB
		$Plan->editDailyPlan(
			$plan_id,
			$_SESSION['school_id'],
			$req_body['record_date'],
			$req_body['dailyPlan_name'],
			$req_body['dailyPlan_type'],
            $plan_img_url);

		$Plan->purgeDailyPlanGoals($args['daily_plan_id']);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoalDailyPlan($v, $args['daily_plan_id']);
			}
		}
        
		/**
		 * First we need to delete and then re-add plan blocks
		 */
		$Plan->deleteDailyPlanBlocks($plan_id);

		$blocks = array();

		for($i = 0; $i < count($req_body['time-block']); $i++) {
			// Make sure that we're adding blocks with content in them
			if($req_body['time-block'][$i] != null || $req_body['description'][$i] != null) {
				array_push($blocks, array(
					'time_block' => $req_body['time-block'][$i],
					'description' => $req_body['description'][$i]
				));
			}
		}
        
		$Plan->addDailyPlanBlocks($plan_id, $blocks);

		/**
		 * Delete all associations, then...
		 * Associate plan with school / room / children
		 * TODO: Optimize the association part
		 */
		$PlanAssociations->purgeAssociations($plan_id, "daily");

		$assoc_arr = array();

		if($req_body['dailyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $req_body[$req_body['dailyPlan_type']];
		}

		$Plan->associateDailyPlan($plan_id, $req_body['dailyPlan_type'], $assoc_arr);

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryDailyPlan'));
	});


	/***************************************************************************
	 * GET 'dailyPlan/{daily_plan_id}'
	 *
	 * View single Daily Plan
	 **************************************************************************/
	$this->get( '/daily/{daily_plan_id}', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Daily Plans';

		$Plan = new Plan($this);
		//$School = new School($this);
		$AssociationGenerator = new AssociationGenerator($this, $args['daily_plan_id'], "daily");

		$view['basic_info'] = $Plan->getPlan($args['daily_plan_id']);
		$view['blocks'] = $Plan->getPlanBlocks($args['daily_plan_id']);

		$view['associations'] = $AssociationGenerator->getAssociations();

		$goals = $Plan->getDailyPlanGoals($args['daily_plan_id']);

		/// todo: should show goals checked for current daily plan at the end?
		//echo '<pre>'; print_r($goals); echo '</pre>';

		foreach ($goals as $goal) {
			$view['formgoals'][] = $goal->goal_id;
		}

		return $this->view->render( $res, 'dailyPlanSingle.html', $view );
	})->setName( 'dailyPlan' );
});