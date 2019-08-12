<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

$app->group('/API/plans', function() use($app) {
	/***************************************************************************
	 * GET '/plans/weekly/'
	 *
	 * View summary page where user can select date to display specific weekly
	 *  Plan
	 **************************************************************************/
	$this->get( '/weekly/all/{year}', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Plan = new Plan($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Weekly Plans';

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$school_weekly_plans = PlanHelper::assembleWeeklyPlanStats($Plan->getAllWeeklyPlansForAYear($_SESSION['school_id'], $args['year']));

		for ($i = 1; $i <= 52; $i++) {
			// Merge created plans into the empty week array
			if(array_key_exists($i,$school_weekly_plans)) {
				$view['weeks'][$i] = $school_weekly_plans[$i];
			}

			// getStartAndEndDate is declared in records.php
			$startEndDates = getStartAndEndDate($i, $args['year']);

			// Convert to UK date format
			$view['weeks'][$i]['start_date'] = date('M d', strtotime($startEndDates[0]));
			$view['weeks'][$i]['end_date'] = date('M d', strtotime($startEndDates[1]));
		}

		$view['year'] = $args['year'];

		return $res->withJson($view['weeks']);
		//return $this->view->render( $res, 'weeklyYearSummary.html', $view );
	})->setName( 'weeklyYearSummary' );


	/***************************************************************************
	 * GET '/plans/weekly/{weekly_plan_id}/'
	 *
	 * Load the page and data for a specific Weekly Plan 
	 **************************************************************************/
	$this->get('/weekly/single/{weekly_plan_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
		$Story = new Story($this);
		$Plan = new Plan($this);
		$School = new School($this);
		$Child = new Child($this);
		$Room = new Room($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Weekly Plan Summary';
		$view['plan'] = $Plan->getWeeklyPlan($args['weekly_plan_id']);
		$view['formdata'] = $view['plan'];

		$startEndDates = getStartAndEndDate($view['plan']->week, $view['plan']->year);
		$view['year'] = $view['plan']->year;
		$view['week'] = $view['plan']->week;
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		if ($view['plan']->school_fk != $_SESSION['school_id']) {
			$this->logger->notice('weekly Plan::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$view['plan']->assocs = $Plan->getAllWeeklyPlanAssociations(
			PlanHelper::assocEntityToDbTable($view['plan']->assoc),
			$view['plan']->weekly_plan_id,
			$view['plan']->assoc);

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$view['group_items'] = [
			'basic' => 'Basic',
			'mon' => 'Monday',
			'tue' => 'Tuesday',
			'wed' => 'Wednesday',
			'thu' => 'Thursday',
			'fri' => 'Friday'
		];

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room(s)',
			'child' => 'Child(ren)',
		];

		$view['emerging_interests'] = [
			'school' => 'School',
			'children' => 'Children'
		];

		$view['blocks'] = $Plan->getWeeklyPlanBlocks($args['weekly_plan_id']);

		$school = $School->getOne($_SESSION['school_id']);
		$categories = $Story->getCategories($school->country_id);

		$goals = $Plan->getWeeklyPlanGoals($args['weekly_plan_id']);

		foreach ($goals as $goal) {
			$view['formgoals'][] = $goal->goal_id;
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

		$view['categories'] = Goals::extractCheckedGoals($view['categories'], $view['formgoals']);

		$data=array('blocks'=>$view['blocks'],'types'=>$view['types'],'plan'=>$view['plan'],'group_items'=>$view['group_items'],'categories'=>$view['categories']);

		return $res->withJSON($data);
		//return $this->view->render($res, 'weeklyPlanSingle.html', $view);
	})->setName( 'singleWeeklyPlan' );


	/***************************************************************************
	 * GET '/plans/weekly/create'
	 *
	 * Load a view where user can create new weekly Plan
	 **************************************************************************/
	$this->get( '/weekly/{week}/{year}/create', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Story = new Story($this);
		$Child = new Child($this);
		$Room = new Room($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Create Weekly Plan';
		$view['year'] = $args['year'];
		$view['week'] = $args['week'];

		$startEndDates = getStartAndEndDate($args['week'], $args['year']);

		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.',
				['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$view['group_items'] = [
			'basic' => 'Basic',
			'mon' => 'Monday',
			'tue' => 'Tuesday',
			'wed' => 'Wednesday',
			'thu' => 'Thursday',
			'fri' => 'Friday'
		];

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];

		$view['emerging_interests'] = [
			'school' => 'School',
			'children' => 'Children'
		];

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

		return $this->view->render($res, 'weeklyPlanCreate.html', $view);
	})->setName( 'createWeeklyPlan' );


	/***************************************************************************
	 * POST '/plans/weekly/create'
	 *
	 * Save data for a new Daily Plan
	 **************************************************************************/
	$this->post('/weekly/{week}/{year}/create', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);

		$data = $req->getParsedBody();

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$this->flash->addMessage('formdata', $data);

		// Create Weekly Plan
		$plan_id = $Plan->addWeeklyPlan($_SESSION['school_id'],
			$data['year'],
			$data['week'],
			$data['plan_name'],
			$data['weeklyPlan_type'],
			$req->getAttribute('user_id'));

		if ($data['goals']) {
			foreach ($data['goals'] as $goal_id) {
				$Plan->createGoalWeeklyPlan($goal_id, $plan_id);
			}
		}

		/**
		 * Add plan blocks
		 */
		$Plan->addWeeklyPlanBlocks($plan_id, $data['day-blocks']);

		/**
		 * Associate plan with school / room / children
		 * TODO: Refactor, as this is used in multiple places
		 */
		$assoc_arr = array();

		if($data['weeklyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['weeklyPlan_type']];
		}

		$Plan->associateWeeklyPlan($plan_id, $data['weeklyPlan_type'], $assoc_arr);

		$this->flash->addMessage('success', 'Weekly plan created.');

		return $res->withJson(['success'=>'Weekly plan created.']);
		//return $res->withStatus(302)->withRedirect($this->router->pathFor('singleWeeklyPlan',['weekly_plan_id' => $plan_id] ));
	});


	/***************************************************************************
	 * POST '/plans/weekly/delete'
	 *
	 * Delete Daily Plan
	 **************************************************************************/
	$this->post('/weekly/delete', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$PlanAssociations = new PlanAssociations($this);

		$data = $req->getParsedBody();

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$this->flash->addMessage('formdata', $data);

		$Plan->purgeWeeklyPlanBlocks($data['weekly_plan_id']);

		$PlanAssociations->purgeAssociations($data['weekly_plan_id'], "weekly");

		$Plan->purgeWeeklyPlanGoals($data['weekly_plan_id']);
		$Plan->deleteWeeklyPlan($data['weekly_plan_id']);

		$this->flash->addMessage('success', 'Weekly plan deleted.');
		return $res->withJson(['success'=>'Weekly plan deleted.']);
		//return $res->withStatus(302)->withRedirect($this->router->pathFor('weeklyYearSummary', ['year' => date('Y')]));
	})->setName( 'deleteWeeklyPlan' );


	/***************************************************************************
	 * GET '/plans/weekly/{daily_plan_id}/edit'
	 *
	 * Load the page and data for a specific Weekly Plan for editing
	 **************************************************************************/
	$this->get('/weekly/{weekly_plan_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$Story = new Story($this);
		$AssociationGenerator = new AssociationGenerator($this, $args['weekly_plan_id'], "weekly");


		$view['plan'] = $Plan->getWeeklyPlan($args['weekly_plan_id']);
		$view['formdata'] = $view['plan'];

		$startEndDates = getStartAndEndDate($view['plan']->week, $view['plan']->year);
		$view['year'] = $view['plan']->year;
		$view['week'] = $view['plan']->week;
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		if ($view['plan']->school_fk != $_SESSION['school_id']) {
			$this->logger->notice('weekly Plan::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$Child = new Child($this);
		$Room = new Room($this);

		$associations = $AssociationGenerator->getAssociations();

		/**
		 * TODO: refactor code
		 */
		$assoc_id_arr = array();
		foreach($associations as $assoc) {
			array_push($assoc_id_arr, $assoc->id);
		}
		$view['assoc_id_arr'] = $assoc_id_arr;

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$view['group_items'] = [
			'basic' => 'Basic',
			'mon' => 'Monday',
			'tue' => 'Tuesday',
			'wed' => 'Wednesday',
			'thu' => 'Thursday',
			'fri' => 'Friday'
		];

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];

		$view['emerging_interests'] = [
			'school' => 'School',
			'children' => 'Children'
		];

		$view['blocks'] = $Plan->getWeeklyPlanBlocks($args['weekly_plan_id']);

		$school = $School->getOne($_SESSION['school_id']);

		$goals = $Plan->getWeeklyPlanGoals($args['weekly_plan_id']);

		foreach ($goals as $goal) {
			$view['formgoals'][] = $goal->goal_id;
		}

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

		return $this->view->render($res, 'weeklyPlanCreate.html', $view);
	})->setName( 'editWeeklyPlan' );


	/***************************************************************************
	 * POST '/plans/weekly/{weekly_plan_id}/edit'
	 *
	 * Save data for an edited Weekly Plan
	 **************************************************************************/
	$this->post('/weekly/{weekly_plan_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$PlanAssociations = new PlanAssociations($this);

		$data = $req->getParsedBody();

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

			return $res->withJson(['error'=>'You don’t have sufficient rights.']);
		}

		$this->flash->addMessage('formdata', $data);

		// Create Weekly Plan
		$Plan->editWeeklyPlan($args['weekly_plan_id'],
			$data['plan_name'],
			$data['weeklyPlan_type']);

		/**
		 * First we need to delete and then re-add plan blocks
		 */
		$Plan->purgeWeeklyPlanBlocks($args['weekly_plan_id']);

		/**
		 * Add plan blocks
		 */
		$Plan->addWeeklyPlanBlocks($args['weekly_plan_id'], $data['day-blocks']);

		$Plan->purgeWeeklyPlanGoals($args['weekly_plan_id']);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoalWeeklyPlan($v, $args['weekly_plan_id']);
			}
		}

		/**
		 * Delete all associations, then...
		 * Associate plan with school / room / children
		 * TODO: Optimize the association part
		 */
		$PlanAssociations->purgeAssociations($args['weekly_plan_id'], "weekly");

		/**
		 * Associate plan with school / room / children
		 * TODO: Refactor, as this is used in multiple places
		 */
		$assoc_arr = array();

		if($data['weeklyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['weeklyPlan_type']];
		}

		$Plan->associateWeeklyPlan($args['weekly_plan_id'], $data['weeklyPlan_type'], $assoc_arr);

		$this->flash->addMessage('success', 'Weekly plan updated.');

		//$plan = $Plan->getWeeklyPlan($args['weekly_plan_id']);

		return $res->withStatus(302)->withRedirect($this->router->pathFor('singleWeeklyPlan',
			['weekly_plan_id' => $args['weekly_plan_id']] ));
	});


	/***************************************************************************
	 * GET '/plans/weekly/{week}/{year}'
	 *
	 * View plans associated to a specific week of the year
	 **************************************************************************/
	$this->get( '/weekly/{week}/{year}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);

		$startEndDates = getStartAndEndDate($args['week'], $args['year']);
		$view['year'] = $args['year'];
		$view['week'] = $args['week'];
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		$view['plans'] = $Plan->getAllWeeklyPlansForAWeek(
			$_SESSION['school_id'],
			$args['year'],
			$args['week']);

		// Grab associations
		foreach($view['plans'] as $key => $plan) {

			$view['plans'][$key]->assocs = $Plan->getAllWeeklyPlanAssociations(
				PlanHelper::assocEntityToDbTable($plan->assoc),
				$plan->weekly_plan_id,
				$plan->assoc);
		}

		return $res->withJson($view['plans']);
		return $this->view->render( $res, 'weeklyPlanSummary.html', $view );
	})->setName( 'weeklyPlanSummary' );
});