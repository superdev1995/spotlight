<?php

use \Carbon\Carbon;

$app->group('/plans', function() use($app) {
	/***************************************************************************
	 * GET 'dailyPlan/'
	 *
	 * View summary page where user can select date to display specific Daily
	 *  Plan
	 **************************************************************************/
	$this->get( '/daily', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Daily Plans';

		$Plan = new Plan($this);
		$Draft = new Draft($this);

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

		// get daily plans drafts
		$view['drafts'] = $Draft->getAllDailyPlans($_SESSION['school_id'], $req->getAttribute('user_id'));
		foreach($view['drafts'] as $key => $draft) {

			$view['drafts'][$key]->assocs = $Draft->getAllDailyPlanAssociations(
				PlanHelper::assocEntityToDbTable($draft->assoc),
				$draft->draft_daily_plan_id,
				$draft->assoc);
		}

		return $this->view->render( $res, 'dailyPlanSummary.html', $view );
	})->setName( 'summaryDailyPlan' );


	/***************************************************************************
	 * GET 'dailyPlan/create'
	 *
	 * Load a view where user can create new Daily Plan
	 **************************************************************************/
	$this->get( '/daily/create[/{draft_id}]', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Create Daily Plan';

		$Plan = new Plan($this);
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
		$draft_id = isset($args['draft_id']) ? $args['draft_id'] : null;
		
		if ($draft_id != null){
			$AssociationGenerator = new AssociationGenerator($this, $draft_id, "draft_daily");
			$view['associations'] = $AssociationGenerator->getAssociations();
	
			$assoc_id_arr = array();
			foreach($view['associations'] as $assoc){
				array_push($assoc_id_arr, $assoc->id);
			}

			$view['assoc_id_arr'] = $assoc_id_arr;
			$goals = $Draft->getDailyPlanGoals($draft_id);

			foreach ($goals as $goal) {
				$view['formgoals'][] = $goal->goal_id;
			}
		}
		else {
			$draft_id = $Draft->addDailyPlan($_SESSION['school_id'], "school", $req->getAttribute('user_id'));
			$Draft->addDailyPlanBlock($draft_id);	
		}

		$view['formdata'] = $Draft->getDailyPlan($draft_id);
		$view['mode'] = 'create';
		$view['draft_id'] = $draft_id;
		$view['blocks'] = $Draft->getDailyPlanBlocks($draft_id);

		return $this->view->render($res, 'dailyPlanCreate.html', $view);
	})->setName( 'createDailyPlan' );


	/***************************************************************************
	 * POST 'dailyPlan/create'
	 *
	 * Save data for a new Daily Plan
	 **************************************************************************/
	$this->post('/daily/create[/{draft_id}]', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$Media = new Media($this);

		$data = $req->getParsedBody();

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

		// remove the corrispondent draft
		$Draft = new Draft($this);
		$Draft->deleteDailyPlan($args['draft_id']);
		$Draft->deleteDailyPlanBlocks($args['draft_id']);
		$Draft->purgeDailyPlanGoals($args['draft_id']);
		$PlanAssociations = new PlanAssociations($this);
		$PlanAssociations->purgeAssociations($args['draft_id'], 'draft_daily');


		$this->flash->addMessage('formdata', $data);
		
		$plan_img_url = "";
		if ($data['plan_img']) {
            $plan_img = $this->uploader->getFile($data['plan_img']);
            
            if ($plan_img->data['is_image']) {
                
                $plan_img_url = $plan_img->getUrl();
            }
		}
        
        // Create daily plan entry in the DB
		$plan_id = $Plan->addDailyPlan( $_SESSION['school_id'],
			$data['record_date'],
			$data['dailyPlan_name'],
			$data['dailyPlan_type'],
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

		for($i = 0; $i < count($data['time-block']); $i++) {
			// Make sure that we're adding blocks with content in them
			if($data['time-block'][$i] != null || $data['description'][$i] != null) {
				array_push($blocks, array(
					'time_block' => $data['time-block'][$i],
					'description' => $data['description'][$i]
				));
			}
		}

		$Plan->addDailyPlanBlocks($plan_id, $blocks);

		$assoc_arr = array();

		if($data['dailyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['dailyPlan_type']];
		}
        
		$Plan->associateDailyPlan($plan_id, $data['dailyPlan_type'], $assoc_arr);

		if($data['videos']){
			$Video = new Video($this);

			$this->logger->debug('Video files found.', [ 'group' => $data['videos'] ]);

			$group = $this->uploader->getGroup($data['videos']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url = $file->getUrl();

				$this->logger->debug('Saved video.', [ 'daily_plan_id' => $plan_id, 'url' => $url ]);

				$video_id = $Video->create($url, $file->data['mime_type'], $_SESSION['school_id']);
				$Plan->createDailyPlanVideo($video_id, $plan_id);
			}
		}

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryDailyPlan'));
	});

	/***************************************************************************
	 * POST 'dailyPlan/draft/autosave/{draft_id}'
	 *
	 * Autosave the current daily plan as a draft
	 **************************************************************************/
	$this->post('/daily/draft/autosave/{draft_id}', function( $req, $res, $args ) use ( $app ){
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
			case 'dailyPlan_type':
				$PlanAssociations = new PlanAssociations($this);
				$PlanAssociations->purgeAssociations($draftId, 'draft_daily');
				$Draft->editDailyPlanType( $draftId, $data['value']);
				break;

			case 'dailyPlan_name':
				$Draft->editDailyPlanName( $draftId, $data['value']);
				break;
	
			case 'record_date':
				$Draft->editDailyPlanDate( $draftId, $data['value']);
				break;

			case 'img_url':
				// delete old image
				$prevImage = $Draft->getDailyPlan($draftId)->plan_img_url;
				if($prevImage != null && $prevImage != $data['value'])
				{
					$file = $this->uploader->getFile($prevImage);
					$file->delete();
				}

				$Draft->editDailyPlanImgUrl( $draftId, $data['value']);
				break;
				
			case 'block_added':
				$res_data['block_id'] = $Draft->addDailyPlanBlock( $draftId );
				break;

			case 'block_deleted':
				$Draft->deleteDailyPlanBlock( $draftId, $data['block_id'] );
				break;

			case 'time_block':
				$Draft->editDailyPlanTimeBlock($data['block_id'], $data['value'] );
				break;

			case 'block_description':
				$Draft->editDailyPlanBlockDescription($data['block_id'], $data['value'] );
				break;

			case 'assoc':
				if($data['is_checked'])
					$Draft->associateDailyPlan($draftId, $data['type'], $data['assoc_id']);
				else{
					$Draft->deassociateDailyPlan($draftId, $data['type'], $data['assoc_id']);
				}
				break;

			case 'goal':
				if($data['is_checked'])
					$Draft->addGoalDailyPlan($draftId, $data['goal_id']);
				else{
					$Draft->removeGoalDailyPlan($draftId, $data['goal_id']);
				}
				break;

			case 'videos':
				// delete old files
				$prevGroupUrl = $Draft->getDailyPlan($draftId)->video_group_url;
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
			
				$Draft->editDailyPlanVideoGroupUrl($draftId, $data['value']);
				break;
		}

		$Draft->updateDailyPlan($draftId);

		return $res->withJson($res_data, 201);
	})->setName('dailyPlanAutosave');



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

		$plan = $Plan->getDailyPlan($data['daily_plan_id']);
		if($plan->plan_img_url){
			$file = $this->uploader->getFile($plan->plan_img_url);
			$file->delete();
		}
		$Plan->purgeDailyPlanGoals($data['daily_plan_id']);
		$Plan->deleteDailyPlan($data['daily_plan_id']);
		$Plan->deleteDailyPlanBlocks($data['daily_plan_id']);

		$Video = new Video($this);
		$videos = $Video->getAllAssociatedVideos('daily_plan', $data['daily_plan_id']);

		foreach($videos as $video){
			$file = $this->uploader->getFile($video->video_url);
			$file->delete();
		}

		$Video->deleteAllAssociatedVideos('daily_plan', $data['daily_plan_id']);

		$this->flash->addMessage('success', 'Daily plan has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryDailyPlan'));
	})->setName( 'deleteDailyPlan' );


	/***************************************************************************
	 * POST 'dailyPlan/draft/delete'
	 *
	 * Delete Daily Plan Draft
	 **************************************************************************/
	$this->post('/daily/draft/delete', function( $req, $res, $args ) use ( $app ){
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

		$draft = $Draft->getDailyPlan($data['draft_id']);
		if($draft->plan_img_url){
			$file = $this->uploader->getFile($draft->plan_img_url);
			$file->delete();
		}

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

		$Draft->deleteDailyPlan($data['draft_id']);
		$Draft->deleteDailyPlanBlocks($data['draft_id']);
		$Draft->purgeDailyPlanGoals($data['draft_id']);
		$PlanAssociations = new PlanAssociations($this);
		$PlanAssociations->purgeAssociations($data['draft_id'], 'draft_daily');

		$this->flash->addMessage('success', 'Daily plan draft has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryDailyPlan'));
	})->setName( 'deleteDraftDailyPlan' );


	/***************************************************************************
	 * GET 'dailyPlan/{daily_plan_id}/edit'
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

		$view['daily_plan_id'] = $args['daily_plan_id'];
		$daily_plan = $Plan->getDailyPlan($args['daily_plan_id']);

		if ($daily_plan->school_fk != $_SESSION['school_id']) {
			$this->logger->notice('daily Plan::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('summaryDailyPlan'));
		}

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);
		$view['formdata'] = $daily_plan;
		$view['blocks'] = $Plan->getDailyPlanBlocks($args['daily_plan_id']);
		$view['associations'] = $AssociationGenerator->getAssociations();
		$view['mode'] = 'edit';

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

		$goals = $Plan->getDailyPlanGoals($args['daily_plan_id']);

		foreach ($goals as $goal) {
			$view['formgoals'][] = $goal->goal_id;
		}

		$Video = new Video($this);
		$date = Carbon::now()->toDateString();
		$view['num_videos_permitted'] = 8 - count($Video->getVideosInADay($date, $_SESSION['school_id']));

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
		$Media = new Media($this);
        
        $url = $Plan->getDailyPlan($args['daily_plan_id'])->plan_img_url;
        
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
				
                $plan_img_url = $plan_img->getUrl();
                if ($url && $url != $plan_img_url) {
                    $this->logger->debug('Attempt to delete old image.', [ 'url' => $url]);
                    
                    $old_img = $this->uploader->getFile($url);
                    $old_img->delete();
                }
            }
		}
		
		$Video = new Video($this);
		$videos = $Video->getAllAssociatedVideos('daily_plan', $args['daily_plan_id']);

		foreach($videos as $video){
			$file = $this->uploader->getFile($video->video_url);
			$file->delete();
		}

		$Video->deleteAllAssociatedVideos('daily_plan', $args['daily_plan_id']);

		if($data['videos']){
			$this->logger->debug('Video files found.', [ 'group' => $data['videos'] ]);

			$group = $this->uploader->getGroup($data['videos']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url = $file->getUrl();

				$this->logger->debug('Saved video.', [ 'daily_plan_id' => $args['daily_plan_id'], 'url' => $url ]);

				$video_id = $Video->create($url, $file->data['mime_type'], $_SESSION['school_id']);
				$Plan->createDailyPlanVideo($video_id, $args['daily_plan_id']);
			}
		}
        
		// Edit daily plan entry in the DB
		$Plan->editDailyPlan(
			$plan_id,
			$_SESSION['school_id'],
			$data['record_date'],
			$data['dailyPlan_name'],
			$data['dailyPlan_type'],
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

		for($i = 0; $i < count($data['time-block']); $i++) {
			// Make sure that we're adding blocks with content in them
			if($data['time-block'][$i] != null || $data['description'][$i] != null) {
				array_push($blocks, array(
					'time_block' => $data['time-block'][$i],
					'description' => $data['description'][$i]
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

		if($data['dailyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['dailyPlan_type']];
		}

		$Plan->associateDailyPlan($plan_id, $data['dailyPlan_type'], $assoc_arr);

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

		$view['basic_info'] = $Plan->getDailyPlan($args['daily_plan_id']);
		$view['blocks'] = $Plan->getDailyPlanBlocks($args['daily_plan_id']);

		$view['associations'] = $AssociationGenerator->getAssociations();

		$goals = $Plan->getDailyPlanGoals($args['daily_plan_id']);

		/// todo: should show goals checked for current daily plan at the end?
		//echo '<pre>'; print_r($goals); echo '</pre>';

		foreach ($goals as $goal) {
			$view['formgoals'][] = $goal->goal_id;
		}

		$Video = new Video($this);
		$view['videos'] = $Video->getAllAssociatedVideos('daily_plan', $args['daily_plan_id']);

		return $this->view->render( $res, 'dailyPlanSingle.html', $view );
	})->setName( 'dailyPlan' );
});