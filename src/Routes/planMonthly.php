<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

use \Carbon\Carbon;

$app->group('/plans', function() use($app) {

	/***************************************************************************
	 * GET '/plans/monthly/{year:[0-9]+}/all'
	 *
	 * View summary page where user can select date to display specific Monthly
	 *  Plan
	 **************************************************************************/
	$this->get( '/monthly/all/{year:[0-9]+}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
		$Draft = new Draft($this);

		$view['title'] = "Monthly Plans";
		$view['flash'] = $this->flash->getMessages();
		
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
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

		$view['months_drafts'] = PlanHelper::assembleMonthlyPlanStats($Draft->getAllMonthlyPlansForAYear($_SESSION['school_id'], $req->getAttribute('user_id'), $args['year']));
		$view['months_completed'] = PlanHelper::assembleMonthlyPlanStats($Plan->getAllMonthlyPlansForAYear($_SESSION['school_id'], $args['year']));
		$view['year'] = $args['year'];


		return $this->view->render( $res, 'monthlyYearSummary.html', $view );
	})->setName( 'monthlyYearSummary' );



	/***************************************************************************
	 * GET '/plans/monthly/create[/{draft_id}]'
	 *
	 * Load a view where user can create new Monthly Plan
	 **************************************************************************/
	$this->get( '/monthly/{month:[0-9]+}/{year:[0-9]+}/create[/{draft_id}]', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Story = new Story($this);
		$Form = new Form($this);
		$Room = new Room($this);
		$Child = new Child($this);
		
		$view['title'] = "Create Monthly Plans";
		$view['flash'] = $this->flash->getMessages();
		$view['year'] = $args['year'];
		$view['month'] = $args['month'];


		/**
		 * // TODO: REFACTOR
		 */
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];

		$school = $School->getOne($_SESSION['school_id']);

		if ($school->country_id == 'US') {
			$view['country_form'] = $Form->retrieveBlocksByCountrySubdivision('monthly_plan_details', $school->country_subdivision_id);
			$categories = $Story->getCategoriesUS($school->country_id,$school->country_subdivision_id);
		} else {
			// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
			$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);
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
			$AssociationGenerator = new AssociationGenerator($this, $draft_id, "draft_monthly");
			$view['associations'] = $AssociationGenerator->getAssociations();
			$assoc_id_arr = array();
			foreach($view['associations'] as $assoc){
				array_push($assoc_id_arr, $assoc->id);
			}

			$view['assoc_id_arr'] = $assoc_id_arr;
			$goals = $Draft->getMonthlyPlanGoals($draft_id);

			foreach ($goals as $goal) {
				$view['formgoals'][] = $goal->goal_id;
			}
		}
		else {
			$draft_id = $Draft->addMonthlyPlan($_SESSION['school_id'], $req->getAttribute('user_id'), $args['year'], $args['month']);
		}

		$view['formdata'] = $Draft->getMonthlyPlan($draft_id);
		$view['formdata']->month_textual = Dates::convertToReadableMonth($args['month']);
		$view['formdata']->plan_public = 1;
		$view['mode'] = 'create';
		$view['draft_id'] = $draft_id;

		return $this->view->render( $res, 'monthlyPlanCreate.html', $view );
	})->setName( 'createMonthlyPlan' );



	/***************************************************************************
	 * POST '/monthly/{month:[0-9]+}/{year:[0-9]+}/create/{draft_id}'
	 *
	 * Save data for a new Monthly Plan
	 **************************************************************************/
	$this->post('/monthly/{month:[0-9]+}/{year:[0-9]+}/create/{draft_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$Media = new Media($this);
		$Child = new Child($this);
		$Room = new Room($this);
		$School = new School($this);
		$Timeline = new Timeline($this);

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}


		$data = $req->getParsedBody();
		$data['school_id'] = $_SESSION['school_id'];
		$data['month'] = $args['month'];
		$data['year'] = $args['year'];
		$data['public'] = $data['public'] ? 1 : 0;

		$plan_id = $Plan->createMonthlyPlan($data);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoal($v, $plan_id);
			}
		}

		$this->logger->info('Plan created.', [ 'monthly_plan_id' => $plan_id, 'user_id' => $req->getAttribute('user_id') ]);
		$this->flash->addMessage('success', 'Monthly plan created.');

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

				$this->logger->debug('Saved media.', [ 'monthly_plan_id' => $args['monthly_plan_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail->getUrl() ]);

				$media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail->getUrl(), $resized_full->data['mime_type']);
				$Plan->createMonthlyMedia($media_id, $plan_id);
			}
		}

		/**
		 * Associate plan with school / room / children
		 * TODO: Refactor, as this is used in multiple places
		 */
		$assoc_arr = array();

		if($data['monthlyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['monthlyPlan_type']];
		}

		$Plan->associateMonthlyPlan($plan_id, $data['monthlyPlan_type'], $assoc_arr);

		$children = array();
		if($data['monthlyPlan_type'] == 'school'){	
			$children = $Child->getAll($_SESSION['school_id']);
		}
		elseif($data['monthlyPlan_type'] == 'room'){
			foreach($data['room'] as $room){
				foreach($Room->getChildren($room) as $child){
					$children[] = $child;
				}
			}
		}
		else {
			foreach($data['child'] as $child){
				$children[] = $Child->getOne($child);
			}
		}



		foreach ($children as $child) {
				
			$Timeline->create($req->getAttribute('user_id'), $child->child_id,
				'monthlyPlan',
				$plan_id,
				$data['public'],
				$data['comment']);

			if ($data['public'] == 1) {
                foreach ($Child->getParents($child->child_id) as $parent) {
                    if (!$parent->user_notify_record) {
                        continue;
                    }

                    $this->mailer->send('planNotify.html', [
                        'to' => $parent->user_email,
                        'subject' => 'A new monthy plan has been created for your child',
                        'first_name' => $parent->user_first_name,
                        'user' => $req->getAttribute('user'),
						'child' => $child,
						'year'=> $data['year']
                    ]);

                    $this->logger->info('Notification sent.', [ 'email' => $parent->user_email ]);
                }
			}
		}

		if($data['videos']){
			$Video = new Video($this);

			$this->logger->debug('Video files found.', [ 'group' => $data['videos'] ]);

			$group = $this->uploader->getGroup($data['videos']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url = $file->getUrl();

				$this->logger->debug('Saved video.', [ 'monthly_plan_id' => $plan_id, 'url' => $url ]);

				$video_id = $Video->create($url, $file->data['mime_type'], $_SESSION['school_id']);
				$Plan->createMonthlyPlanVideo($video_id, $plan_id);
			}
		}

		// remove the corrispondent draft
		$Draft = new Draft($this);
		$Draft->deleteMonthlyPlan($args['draft_id']);
		$Draft->purgeMonthlyPlanGoals($args['draft_id']);
		$PlanAssociations = new PlanAssociations($this);
		$PlanAssociations->purgeAssociations($args['draft_id'], 'draft_monthly');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('singleMonthlyPlan',
		['monthly_plan_id' => $plan_id] ));
	});

	/***************************************************************************
	 * POST 'monthly/draft/autosave/{draft_id}'
	 *
	 * Autosave the current monthly plan as a draft
	 **************************************************************************/
	$this->post('/monthly/draft/autosave/{draft_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
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
			case 'monthlyPlan_type':
				$PlanAssociations = new PlanAssociations($this);
				$PlanAssociations->purgeAssociations($draftId, 'draft_monthly');
				$Draft->editMonthlyPlanType( $draftId, $data['value']);
				break;

			case 'public':
				$Draft->editMonthlyPlanPublic( $draftId, $data['is_checked'] );
				break;

			case 'form_block':
				$Draft->editMonthlyPlanFormBlock( $draftId, $data['column'], $data['value']);
				break;
			
			case 'media':
				// delete old files
				$prevGroupUrl = $Draft->getMonthlyPlan($draftId)->group_url;
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
			
				$Draft->editMonthlyPlanGroupUrl($draftId, $data['value']);
				break;

			case 'videos':
				// delete old files
				$prevGroupUrl = $Draft->getMonthlyPlan($draftId)->video_group_url;
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
			
				$Draft->editMonthlyPlanVideoGroupUrl($draftId, $data['value']);
				break;

			case 'assoc':
				if($data['is_checked'])
					$Draft->associateMonthlyPlan($draftId, $data['type'], $data['assoc_id']);
				else{
					$Draft->deassociateMonthlyPlan($draftId, $data['type'], $data['assoc_id']);
				}
				break;

			case 'goal':
				if($data['is_checked'])
					$Draft->addGoalMonthlyPlan($draftId, $data['goal_id']);
				else{
					$Draft->removeGoalMonthlyPlan($draftId, $data['goal_id']);
				}
				break;
		}

		$Draft->updateMonthlyPlan($draftId);

		return $res->withJson($res_data, 201);
	})->setName('monthlyPlanAutosave');

	/***************************************************************************
	 * POST 'plans/monthly/draft/delete'
	 *
	 * Delete Monthly Plan Draft
	 **************************************************************************/
	$this->post('/monthly/draft/delete', function( $req, $res, $args ) use ( $app ){
		$Draft = new Draft($this);
		$School = new School($this);

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

		
		$data = $req->getParsedBody();
		$this->flash->addMessage('formdata', $data);

		$draft = $Draft->getMonthlyPlan($data['draft_id']);
		$Draft->deleteMonthlyPlan($data['draft_id']);
		$Draft->purgeMonthlyPlanGoals($data['draft_id']);

		// delete media
		$groupUrl = $draft->group_url;
		if($groupUrl != null)
		{
			$group = $this->uploader->getGroup($groupUrl);
			$files = $group->getFiles();
			foreach ($files as $file) {
				$file->delete();
			}
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

		$PlanAssociations = new PlanAssociations($this);
		$PlanAssociations->purgeAssociations($data['draft_id'], 'draft_monthly');

		$this->flash->addMessage('success', 'Monthly plan draft has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('monthlyPlanSummary', [
			'year' => $draft->year, 'month' => $draft->month
		]));
	})->setName( 'deleteDraftMonthlyPlan' );

	/***************************************************************************
	 * POST 'plans/monthly/delete'
	 *
	 * Delete Daily Plan
	 **************************************************************************/
	$this->post('/monthly/delete', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$Timeline = new Timeline($this);
		$PlanAssociations = new PlanAssociations($this);

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}
		
		$data = $req->getParsedBody();
		$this->flash->addMessage('formdata', $data);
		
		$plan = $Plan->getMonthlyPlan($data['monthly_plan_id']);
		
		// delete all media
		$Media = new Media($this);
		$medias = $Media->getAllAssociatedMedia('monthly_plan', $data['monthly_plan_id']);
		foreach($medias as $media){
			$file = $this->uploader->getFile($media->media_full_url);
			$file->delete();

			$file = $this->uploader->getFile($media->media_thumbnail_url);
			$file->delete();
		}
		$Media->deleteAllAssociatedMedia('monthly_plan', $data['monthly_plan_id']);

		// delete all videos
		$Video = new Video($this);
		$videos = $Video->getAllAssociatedVideos('monthly_plan', $data['monthly_plan_id']);
		foreach($videos as $video){
			$file = $this->uploader->getFile($video->video_url);
			$file->delete();
		}
		$Media->deleteAllAssociatedVideos('monthly_plan', $data['monthly_plan_id']);

		$Plan->purgeMonthlyPlanGoals($data['monthly_plan_id']);
		$Plan->deleteMonthlyPlan($data['monthly_plan_id']);
		$PlanAssociations->purgeAssociations($data['monthly_plan_id'], "monthly");
		$Timeline->purge('monthlyPlan',$data['monthly_plan_id']);

		return $res->withStatus(302)->withRedirect($this->router->pathFor('monthlyPlanSummary', [
			'year' => $plan->year, 'month' => $plan->month
		]));
	})->setName( 'deleteMonthlyPlan' );



	/***************************************************************************
	 * GET '/plans/monthly/{daily_plan_id}/edit'
	 *
	 * Load the page and data for a specific Monthly Plan for editing
	 **************************************************************************/
	$this->get('/monthly/{monthly_plan_id:[0-9]+}/edit', function( $req, $res, $args ) use ( $app ){
		$Story = new Story($this);
		$School = new School($this);
		$Plan = new Plan($this);
		$Form = new Form($this);
		$AssociationGenerator = new AssociationGenerator($this, $args['monthly_plan_id'], "monthly");
		
		$view['title'] = "Edit Monthly Plan";
		$view['flash'] = $this->flash->getMessages();

		$view['monthly_plan_id'] = $args['monthly_plan_id'];
		$monthly_plan = $Plan->getMonthlyPlan($args['monthly_plan_id']);
		$view['formdata'] = $monthly_plan;
		$view['mode'] = 'edit';

		$view['year'] = $monthly_plan->year;
		$view['month'] = $monthly_plan->month;

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
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

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];

	
		$view['formdata']->month_textual = Dates::convertToReadableMonth($monthly_plan->month);

		$Media = new Media($this);
		$view['formdata']->medias = $Media->getAllAssociatedMedia('monthly_plan', $args['monthly_plan_id']);

		/**
		 * // TODO: REFACTOR
		 */
		$school = $School->getOne($_SESSION['school_id']);

		if ($school->country_id == 'US') {
			$view['country_form'] = $Form->retrieveBlocksByCountrySubdivision('monthly_plan_details', $school->country_subdivision_id);
			$categories = $Story->getCategoriesUS($school->country_id,$school->country_subdivision_id);
		} else {
			// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
			$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);
			$categories = $Story->getCategories($school->country_id);
		}

		

		$goals = $Plan->getMonthlyPlanGoals($args['monthly_plan_id']);

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
		
		$Video = new Video($this);
		$date = Carbon::now()->toDateString();
		$view['num_videos_permitted'] = 8 - count($Video->getVideosInADay($date, $_SESSION['school_id']));

		return $this->view->render( $res, 'monthlyPlanCreate.html', $view );
	})->setName( 'editMonthlyPlan' );




	/***************************************************************************
	 * POST '/plans/monthly/{monthly_plan_id}/edit'
	 *
	 * Save data for an edited Monthly Plan
	 **************************************************************************/
	$this->post('/monthly/{monthly_plan_id:[0-9]+}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$Room = new Room($this);
		$Media = new Media($this);
		$Timeline = new Timeline($this);
		$Child = new Child($this);
		$PlanAssociations = new PlanAssociations($this);

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		$data = $req->getParsedBody();
		
		$data['public'] = $data['public'] ? 1 : 0;

		$children = array();
		if($data['monthlyPlan_type'] == 'school'){	
			$children = $Child->getAll($_SESSION['school_id']);
		}
		elseif($data['monthlyPlan_type'] == 'room'){
			foreach($data['room'] as $room){
				foreach($Room->getChildren($room) as $child){
					$children[] = $child;
				}
			}
		}
		else {
			foreach($data['child'] as $child){
				$children[] = $Child->getOne($child);
			}
		}

		$Timeline->updateVisibility('monthlyPlan',$args['monthly_plan_id'],$data['public']);

		if($data['public']==1){
			$timeline=$Timeline->getOnewithLinkedID('monthlyPlan',$args['monthly_plan_id']);

			if($timeline->timeline_public==0){

				foreach ($children as $child) {
					
					foreach ($Child->getParents($child->child_id) as $parent) {
						if (!$parent->user_notify_record) {
							continue;
						}
		
						$this->mailer->send('planNotify.html', [
							'to' => $parent->user_email,
							'subject' => 'A new monthy plan has been created for your child',
							'first_name' => $parent->user_first_name,
							'user' => $req->getAttribute('user'),
							'child' => $child,
							'year'=> $data['year']
						]);
		
						$this->logger->info('Notification sent.', [ 'email' => $parent->user_email ]);
		
					}
					
				}
			}

			
		}
		$plan = $Plan->getMonthlyPlan($args['monthly_plan_id']);
		
		if ($data['media'] && $data['media'] != $plan->plan_img_url) {
			$Media = new Media($this);
			$medias = $Media->getAllAssociatedMedia('monthly_plan', $args['monthly_plan_id']);

			foreach($medias as $media){
				$file = $this->uploader->getFile($media->media_full_url);
				$file->delete();

				$file = $this->uploader->getFile($media->media_thumbnail_url);
				$file->delete();
			}

			$Media->deleteAllAssociatedMedia('monthly_plan', $args['monthly_plan_id']);

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

				$this->logger->debug('Saved media.', [ 'monthly_plan_id' => $args['monthly_plan_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail->getUrl() ]);

				$media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail->getUrl(), $resized_full->data['mime_type']);
				$Plan->createMonthlyMedia($media_id, $args['monthly_plan_id']);
			}
		}

		$Video = new Video($this);
		$videos = $Video->getAllAssociatedVideos('monthly_plan', $args['monthly_plan_id']);

		foreach($videos as $video){
			$file = $this->uploader->getFile($video->video_url);
			$file->delete();
		}

		$Video->deleteAllAssociatedVideos('monthly_plan', $args['monthly_plan_id']);

		if($data['videos']){
			$this->logger->debug('Video files found.', [ 'group' => $data['videos'] ]);

			$group = $this->uploader->getGroup($data['videos']);
			$files = $group->getFiles();

			foreach ($files as $file) {
				$url = $file->getUrl();

				$this->logger->debug('Saved video.', [ 'monthly_plan_id' => $args['monthly_plan_id'], 'url' => $url ]);

				$video_id = $Video->create($url, $file->data['mime_type'], $_SESSION['school_id']);
				$Plan->createMonthlyPlanVideo($video_id, $args['monthly_plan_id']);
			}
		}

		$Plan->updateMonthlyPlan($args['monthly_plan_id'], $data);
		$Plan->purgeMonthlyPlanGoals($args['monthly_plan_id']);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoal($v, $args['monthly_plan_id']);
			}
		}


		$PlanAssociations->purgeAssociations($args['monthly_plan_id'], "monthly");

		$assoc_arr = array();

		if($data['monthlyPlan_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['monthlyPlan_type']];
		}

		$Plan->associateMonthlyPlan($args['monthly_plan_id'], $data['monthlyPlan_type'], $assoc_arr);

		$this->logger->info('Plan updated.', [ 'monthly_plan_id' => $args['monthly_plan_id'], 'user_id' => $req->getAttribute('user_id') ]);
		$this->flash->addMessage('success', 'Monthly plan has been updated.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('singleMonthlyPlan',
		['monthly_plan_id' => $args['monthly_plan_id']] ));
	});



	/***************************************************************************
	 * GET '/plans/monthly/single/{monthly_plan_id:[0-9]+}'
	 *
	 * View single Monthly Plan
	 **************************************************************************/
	$this->get( '/monthly/single/{monthly_plan_id:[0-9]+}[/{child_id:[0-9]+}]', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
		$Child = new Child($this);
		$Room = new Room($this);
		$Story = new Story($this);
		$Form = new Form($this);

		$view['title'] = "Monthly Plan Summary";
		$view['flash'] = $this->flash->getMessages();


		if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

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

		$view['plan'] = $Plan->getMonthlyPlan($args['monthly_plan_id']);
		$view['year'] = $view['plan']->year;
		$view['month'] = $view['plan']->month;

		$view['plan']->assocs = $Plan->getAllMonthlyPlanAssociations(
			PlanHelper::assocEntityToDbTable($view['plan']->assoc),
			$view['plan']->monthly_plan_id,
			$view['plan']->assoc);

		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);
		
		$view['types'] = [
			'school' => 'School',
			'room' => 'Room(s)',
			'child' => 'Child(ren)'
		];

		/**
		 * // TODO: REFACTOR
		 */
		$school = $School->getOne($_SESSION['school_id']);

		if ($school->country_id == 'US') {
			$view['country_form'] = $Form->retrieveBlocksByCountrySubdivision('monthly_plan_details', $school->country_subdivision_id);
			$categories = $Story->getCategoriesUS($school->country_id, $school->country_subdivision_id);
		} else {
			// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
			$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);
			$categories = $Story->getCategories($school->country_id);
		}

		

		$goals = $Plan->getMonthlyPlanGoals($view['plan']->monthly_plan_id);

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

		/**
		 * // TODO: REFACTOR
		 */

		$view['plan']->month_textual = Dates::convertToReadableMonth($view['plan']->month);
		
		$Media = new Media($this);
		$view['plan']->medias = $Media->getAllAssociatedMedia('monthly_plan', $view['plan']->monthly_plan_id);

		$Video = new Video($this);
		$view['videos'] = $Video->getAllAssociatedVideos('monthly_plan', $args['monthly_plan_id']);

		if($args['child_id']){
			$Accident = new Accident($this);
			$Checklist = new Checklist($this);
			
			$child = $Child->getOne($args['child_id']);
			$view['child'] = $child;
			$view['children'] = $Child->getAll($child->school_id);
			$view['story_count'] = $Story->getChildCount($args['child_id']);
			$view['checklist_count'] = $Checklist->getChildCount($args['child_id']);
			$view['accident_count'] = $Accident->getChildCount($args['child_id']);
			$view['navigation'] = 'monthlyPlans';
		}

		//return $res->withJSON($view);
		return $this->view->render( $res, 'monthlyPlanSingle.html', $view );
	})->setName( 'singleMonthlyPlan' );

	/***************************************************************************
	 * GET '/{child_id}/plans/{month:[0-9]+}/{year:[0-9]+}'
	 *
	 * View single Monthly Plan for parents
	 **************************************************************************/
	$this->get( '/{child_id}/monthly/{month:[0-9]+}/{year:[0-9]+}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
		$Child = new Child($this);
		$Story = new Story($this);
		$Form = new Form($this);

		$view['title'] = "Monthly Plan";
		$view['flash'] = $this->flash->getMessages();


		if (!$Child->getParent($args['child_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('Child::getParent failed.', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

		$child = $Child->getOne($args['child_id']);

		$view['plan'] = $Plan->getMonthlyPlan($args['year'], $args['month'], $child->school_id);

		if (!$view['plan']->plan_public) {
            $this->logger->notice('Access attempt to non shared monthly plan', ['child_id' => $args['child_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

		/**
		 * // TODO: REFACTOR
		 */
		$school = $School->getOne($child->school_id);

		if ($school->country_id == 'US') {
			$view['country_form'] = $Form->retrieveBlocksByCountrySubdivision('monthly_plan_details', $school->country_subdivision_id);
			$categories = $Story->getCategoriesUS($school->country_id,$school->country_subdivision_id);
		} else {
			// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
			$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);
			$categories = $Story->getCategories($school->country_id);
		}
		

		$goals = $Plan->getMonthlyPlanGoals($view['plan']->monthly_plan_id);

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

		/**
		 * // TODO: REFACTOR
		 */

		$view['plan']->month_textual = Dates::convertToReadableMonth($view['plan']->month);
		$Media = new Media($this);
		$view['plan']->medias = $Media->getAllAssociatedMedia('monthly_plan', $view['plan']->monthly_plan_id);

		return $this->view->render( $res, 'monthlyPlanSingle.html', $view );
	})->setName( 'childMonthlyPlan' );

	/***************************************************************************
	 * GET '/plans/monthly/{month:[0-9]+}/{year:[0-9]+}'
	 *
	 * View plans associated to a specific month of the year
	 **************************************************************************/
	$this->get( '/monthly/{month:[0-9]+}/{year:[0-9]+}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$Draft = new Draft($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Monthly Plans';

		$view['year'] = $args['year'];
		$view['month'] = $args['month'];

		$view['plans'] = $Plan->getAllMonthlyPlansForAMonth(
			$_SESSION['school_id'],
			$args['year'],
			$args['month']);

		// Grab associations
		foreach($view['plans'] as $key => $plan) {

			$view['plans'][$key]->assocs = $Plan->getAllMonthlyPlanAssociations(
				PlanHelper::assocEntityToDbTable($plan->assoc),
				$plan->monthly_plan_id,
				$plan->assoc);
		}

		// get monthly plans drafts
		$view['drafts'] = $Draft->getAllMonthlyPlansForAMonth($_SESSION['school_id'], $req->getAttribute('user_id'), $args['year'], $args['month']);
		foreach($view['drafts'] as $key => $draft) {
			$view['drafts'][$key]->assocs = $Draft->getAllMonthlyPlanAssociations(
				PlanHelper::assocEntityToDbTable($draft->assoc),
				$draft->draft_monthly_plan_id,
				$draft->assoc);
		}

		return $this->view->render( $res, 'monthlyPlanSummary.html', $view );
	})->setName( 'monthlyPlanSummary' );
});