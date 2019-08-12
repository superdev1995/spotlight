<?php
use Dompdf\Exception;

use \Carbon\Carbon;

$app->group('/plans', function() use($app) {
	/***************************************************************************
	 * GET '/plans/weekly/'
	 *
	 * View summary page where user can select date to display specific weekly
	 *  Plan
	 **************************************************************************/
	$this->get( '/weekly/all/{year:[0-9]+}', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Plan = new Plan($this);
		$Draft = new Draft($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Weekly Plans';

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		for ($i = 1; $i <= 52; $i++) {
			// getStartAndEndDate is declared in records.php
			$startEndDates = getStartAndEndDate($i, $args['year']);

			// Convert to UK date format
			$view['weeks'][$i]['start_date'] = date('M d', strtotime($startEndDates[0]));
			$view['weeks'][$i]['end_date'] = date('M d', strtotime($startEndDates[1]));
		}

		$view['weeks_completed'] = PlanHelper::assembleWeeklyPlanStats($Plan->getAllWeeklyPlansForAYear($_SESSION['school_id'], $args['year']));
		$view['weeks_drafts'] = PlanHelper::assembleWeeklyPlanStats(
			$Draft->getAllWeeklyPlansForAYear($_SESSION['school_id'], $req->getAttribute('user_id'), $args['year']));
		$view['year'] = $args['year'];

		return $this->view->render( $res, 'weeklyYearSummary.html', $view );
	})->setName( 'weeklyYearSummary' );


	/***************************************************************************
	 * GET '/plans/weekly/single/{weekly_plan_id:[0-9]+}'
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
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('weeklyYearSummary', ['year' => $view['plan']->year] ));
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

		if ($school->country_id == 'US'){
			$categories = $Story->getCategoriesUS($school->country_id,$school->country_subdivision_id);
		}else{
			$categories = $Story->getCategories($school->country_id);
		}

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
		$Video = new Video($this);
		$view['videos'] = $Video->getAllAssociatedVideos('weekly_plan', $args['weekly_plan_id']);

		return $this->view->render($res, 'weeklyPlanSingle.html', $view);
	})->setName( 'singleWeeklyPlan' );


	/***************************************************************************
	 * GET '/plans/weekly/create[/{draft_id:[0-9]+}]'
	 *
	 * Load a view where user can create new Weekly Plan
	 **************************************************************************/
	$this->get( '/weekly/{week:[0-9]+}/{year:[0-9]+}/create[/{draft_id:[0-9]+}]', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Story = new Story($this);
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
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}

		$Child = new Child($this);

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

		if ($school->country_id == 'US'){
			$categories = $Story->getCategoriesUS($school->country_id,$school->country_subdivision_id);
		}else{
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

		$Video = new Video($this);
		$date = Carbon::now()->toDateString();
		$view['num_videos_permitted'] = 8 - count($Video->getVideosInADay($date, $_SESSION['school_id']));

		
		$Draft = new Draft($this);
		$draft_id = $args['draft_id'];
		
		if ($draft_id != null){
			$AssociationGenerator = new AssociationGenerator($this, $draft_id, "draft_weekly");
			$view['associations'] = $AssociationGenerator->getAssociations();
			$assoc_id_arr = array();
			foreach($view['associations'] as $assoc){
				array_push($assoc_id_arr, $assoc->id);
			}

			$view['assoc_id_arr'] = $assoc_id_arr;
			$goals = $Draft->getWeeklyPlanGoals($draft_id);

			foreach ($goals as $goal) {
				$view['formgoals'][] = $goal->goal_id;
			}

			
			$view['blocks'] = $Draft->getWeeklyPlanBlocks($draft_id);
		}
		else {
			$draft_id = $Draft->addWeeklyPlan($_SESSION['school_id'], $args['year'], $args['week'], "school", $req->getAttribute('user_id'));
		}

		$view['formdata'] = $Draft->getWeeklyPlan($draft_id);
		$view['mode'] = 'create';
		$view['draft_id'] = $draft_id;

		return $this->view->render($res, 'weeklyPlanCreate.html', $view);
	})->setName( 'createWeeklyPlan' );


	/***************************************************************************
	 * POST '/plans/weekly/:week/:year/create/{draft_id:[0-9]+}'
	 *
	 * Save data for a new Weekly Plan
	 **************************************************************************/
	$this->post('/weekly/{week:[0-9]+}/{year:[0-9]+}/create/{draft_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$Media = new Media($this);

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
		
		// remove the corrispondent draft
		$Draft = new Draft($this);
		$Draft->deleteWeeklyPlan($args['draft_id']);
		$Draft->deleteWeeklyPlanBlocks($args['draft_id']);
		$Draft->purgeWeeklyPlanGoals($args['draft_id']);
		$PlanAssociations = new PlanAssociations($this);
		$PlanAssociations->purgeAssociations($args['draft_id'], 'draft_weekly');

		// Create Weekly Plan
		$plan_id = $Plan->addWeeklyPlan($_SESSION['school_id'],
			$data['year'],
			$data['week'],
			$data['weeklyPlan_name'],
			$data['weeklyPlan_type'],
			$req->getAttribute('user_id'),
			$data);

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

		if($data['videos']){
			$Video = new Video($this);

			$this->logger->debug('Video files found.', [ 'group' => $data['videos'] ]);

			$group = $this->uploader->getGroup($data['videos']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url = $file->getUrl();

				$this->logger->debug('Saved video.', [ 'weekly_plan_id' => $plan_id, 'url' => $url ]);

				$video_id = $Video->create($url, $file->data['mime_type'], $_SESSION['school_id']);
				$Plan->createWeeklyPlanVideo($video_id, $plan_id);
			}
		}

		$this->flash->addMessage('success', 'Weekly plan created.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('singleWeeklyPlan',
			['weekly_plan_id' => $plan_id] ));
	});

	/***************************************************************************
	 * POST 'weekly/draft/autosave/{draft_id:[0-9]+}'
	 *
	 * Autosave the current weekly plan as a draft
	 **************************************************************************/
	$this->post('/weekly/draft/autosave/{draft_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
		$Draft = new Draft($this);
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

		$draftId = $args['draft_id'];
		$field = $data['field'];
		$data = json_decode($data['data'], TRUE);

		$res_data = array();

		// save data according to field
		switch($field){
			case 'weeklyPlan_type':
				$PlanAssociations = new PlanAssociations($this);
				$PlanAssociations->purgeAssociations($draftId, 'draft_weekly');
				$Draft->editWeeklyPlanType( $draftId, $data['value']);
				break;

			case 'weeklyPlan_name':
				$Draft->editWeeklyPlanName( $draftId, $data['value']);
				break;
				
			case 'block_added':
				$res_data['block_id'] = $Draft->addWeeklyPlanBlock( $draftId, $data['day'] );
				break;

			case 'block_deleted':
				$Draft->deleteWeeklyPlanBlock($data['block_id'] );
				break;

			case 'block_section':
				$Draft->editWeeklyPlanBlock($data['block_id'], $data['section'], $data['value'] );
				break;

			case 'videos':
				// delete old files
				$prevGroupUrl = $Draft->getWeeklyPlan($draftId)->video_group_url;
				if($prevGroupUrl != null && $prevGroupUrl != $data['value'])
				{
					$prevGroup = $this->uploader->getGroup($prevGroupUrl);
					$newGroup = $this->uploader->getGroup($data['value']);
					$prevFiles = $prevGroup->getFiles();
					$newFiles = $newGroup->getFiles();

					foreach($prevFiles as $prevFile){
						$equal = false;
						foreach($newFiles as $newFile){
							if($newFile->getUrl() == $prevFile->getUrl()){
								$equal = true;
								break;
							}
						}
						if(!$equal)
							$prevFile->delete();
					}
				}
			
				$Draft->editWeeklyPlanVideoGroupUrl($draftId, $data['value']);
				break;

			case 'assoc':
				if($data['is_checked'])
					$Draft->associateWeeklyPlan($draftId, $data['type'], $data['assoc_id']);
				else{
					$Draft->deassociateWeeklyPlan($draftId, $data['type'], $data['assoc_id']);
				}
				break;

			case 'goal':
				if($data['is_checked'])
					$Draft->addGoalWeeklyPlan($draftId, $data['goal_id']);
				else{
					$Draft->removeGoalWeeklyPlan($draftId, $data['goal_id']);
				}
				break;

			case 'keyword':
				$Draft->editWeeklyPlanKeyword($draftId, $data['id'], $data['value']);
				break;
		}

		$Draft->updateWeeklyPlan($draftId);

		return $res->withJson($res_data, 201);
	})->setName('weeklyPlanAutosave');

	/***************************************************************************
	 * POST 'weekly/draft/delete'
	 *
	 * Delete Weekly Plan Draft
	 **************************************************************************/
	$this->post('/weekly/draft/delete', function( $req, $res, $args ) use ( $app ){
		$Draft = new Draft($this);
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

		$draft = $Draft->getWeeklyPlan($data['draft_id']);
		$Draft->deleteWeeklyPlan($data['draft_id']);
		$Draft->deleteWeeklyPlanBlocks($data['draft_id']);
		$Draft->purgeWeeklyPlanGoals($data['draft_id']);
		$PlanAssociations = new PlanAssociations($this);
		$PlanAssociations->purgeAssociations($data['draft_id'], 'draft_weekly');

		// delete videos
		$groupUrl = $draft->video_group_url;
		if($groupUrl != null)
		{
			$group = $this->uploader->getGroup($groupUrl);
			$files = $group->getFiles();
			foreach ($files as $file) {
				$file->delete();
			}
		}

		$this->flash->addMessage('success', 'Weekly plan draft has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('weeklyPlanSummary', [
			'year' => $draft->year, 'week' => $draft->week
		]));
	})->setName( 'deleteDraftWeeklyPlan' );

	/***************************************************************************
	 * POST '/plans/weekly/delete'
	 *
	 * Delete Weekly Plan
	 **************************************************************************/
	$this->post('/weekly/delete', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$PlanAssociations = new PlanAssociations($this);

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

		$plan = $Plan->getWeeklyPlan($data['weekly_plan_id']);
		$Plan->purgeWeeklyPlanBlocks($data['weekly_plan_id']);

		$PlanAssociations->purgeAssociations($data['weekly_plan_id'], "weekly");

		$Plan->purgeWeeklyPlanGoals($data['weekly_plan_id']);
		$Plan->deleteWeeklyPlan($data['weekly_plan_id']);

		$Video = new Video($this);
		$videos = $Video->getAllAssociatedVideos('weekly_plan', $data['weekly_plan_id']);

		foreach($videos as $video){
			$file = $this->uploader->getFile($video->video_url);
			$file->delete();
		}

		$Video->deleteAllAssociatedVideos('weekly_plan', $data['weekly_plan_id']);


		$this->flash->addMessage('success', 'Weekly plan deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('weeklyPlanSummary', 
		[ 'year' => $plan->year, 'week' => $plan->week ]));
	})->setName( 'deleteWeeklyPlan' );


	/***************************************************************************
	 * GET '/plans/weekly/{weekly_plan_id}/edit'
	 *
	 * Load the page and data for a specific Weekly Plan for editing
	 **************************************************************************/
	$this->get('/weekly/{weekly_plan_id:[0-9]+}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$Story = new Story($this);
		$AssociationGenerator = new AssociationGenerator($this, $args['weekly_plan_id'], "weekly");

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Edit Weekly Plan';
		$view['weekly_plan_id'] = $args['weekly_plan_id'];
		$weekly_plan = $Plan->getWeeklyPlan($args['weekly_plan_id']);

		$startEndDates = getStartAndEndDate($weekly_plan->week, $weekly_plan->year);
		$view['formdata'] = $weekly_plan;
		$view['year'] = $weekly_plan->year;
		$view['week'] = $weekly_plan->week;
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		if ($weekly_plan->school_fk != $_SESSION['school_id']) {
			$this->logger->notice('weekly Plan::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('weeklyYearSummary', ['year' => $weekly_plan->year] ));
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

		if ($school->country_id == 'US'){
			$categories = $Story->getCategoriesUS($school->country_id,$school->country_subdivision_id);
		}else{
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

		$Video = new Video($this);
		$date = Carbon::now()->toDateString();
		$view['num_videos_permitted'] = 8 - count($Video->getVideosInADay($date, $_SESSION['school_id']));

		$view['mode'] = 'edit';
		return $this->view->render($res, 'weeklyPlanCreate.html', $view);
	})->setName( 'editWeeklyPlan' );


	/***************************************************************************
	 * POST '/plans/weekly/{weekly_plan_id:[0-9]+}/edit'
	 *
	 * Save data for an edited Weekly Plan
	 **************************************************************************/
	$this->post('/weekly/{weekly_plan_id:[0-9]+}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$PlanAssociations = new PlanAssociations($this);
		$Media = new Media($this);

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

		// Create Weekly Plan
		$Plan->editWeeklyPlan($args['weekly_plan_id'],
			$data['plan_name'],
			$data['weeklyPlan_type'],
			$data);

		$Plan->purgeWeeklyPlanBlocks($args['weekly_plan_id']);

		$Plan->addWeeklyPlanBlocks($args['weekly_plan_id'], $data['day-blocks']);

		$Plan->purgeWeeklyPlanGoals($args['weekly_plan_id']);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoalWeeklyPlan($v, $args['weekly_plan_id']);
			}
		}

		$PlanAssociations->purgeAssociations($args['weekly_plan_id'], "weekly");

		$assoc_arr = array();

		if($data['weeklyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['weeklyPlan_type']];
		}

		$Plan->associateWeeklyPlan($args['weekly_plan_id'], $data['weeklyPlan_type'], $assoc_arr);

		$Video = new Video($this);
		$videos = $Video->getAllAssociatedVideos('weekly_plan', $args['weekly_plan_id']);

		foreach($videos as $video){
			$file = $this->uploader->getFile($video->video_url);
			$file->delete();
		}

		$Video->deleteAllAssociatedVideos('weekly_plan', $args['weekly_plan_id']);

		if($data['videos']){
			$this->logger->debug('Video files found.', [ 'group' => $data['videos'] ]);

			$group = $this->uploader->getGroup($data['videos']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url = $file->getUrl();

				$this->logger->debug('Saved video.', [ 'weekly_plan_id' => $args['weekly_plan_id'], 'url' => $url ]);

				$video_id = $Video->create($url, $file->data['mime_type'], $_SESSION['school_id']);
				$Plan->createWeeklyPlanVideo($video_id, $args['weekly_plan_id']);
			}
		}

		$this->flash->addMessage('success', 'Weekly plan updated.');

		//$plan = $Plan->getWeeklyPlan($args['weekly_plan_id']);

		return $res->withStatus(302)->withRedirect($this->router->pathFor('singleWeeklyPlan',
			['weekly_plan_id' => $args['weekly_plan_id']] ));
	});


	/***************************************************************************
	 * GET '/plans/weekly/{week:[0-9]+}/{year:[0-9]+}'
	 *
	 * View plans associated to a specific week of the year
	 **************************************************************************/
	$this->get( '/weekly/{week:[0-9]+}/{year:[0-9]+}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$Draft = new Draft($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Weekly Plans';

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

		// get weekly plans drafts
		$view['drafts'] = $Draft->getAllWeeklyPlansForAWeek($_SESSION['school_id'], $req->getAttribute('user_id'), $args['year'], $args['week']);
		foreach($view['drafts'] as $key => $draft) {
			$view['drafts'][$key]->assocs = $Draft->getAllWeeklyPlanAssociations(
				PlanHelper::assocEntityToDbTable($draft->assoc),
				$draft->draft_weekly_plan_id,
				$draft->assoc);
		}

		return $this->view->render( $res, 'weeklyPlanSummary.html', $view );
	})->setName( 'weeklyPlanSummary' );
});