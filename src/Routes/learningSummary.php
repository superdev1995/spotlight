<?php

$app->group('/learningSummary', function() use($app) {

	/***************************************************************************
	 * GET '/learningSummary/weekly/'
	 *
	 * View summary page where user can select date to display specific weekly
	 *  Plan
	 **************************************************************************/
	$this->get( '/weekly/all/{year}', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Plan = new Plan($this);
		$LearningSummary = new LearningsSummary($this);
		$Draft = new Draft($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Learning Summary';

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

		$view['weeks_completed'] = LearningSummaryHelper::assembleWeeklySummaryStats($LearningSummary->getAllLearningSummaryForAYear($_SESSION['school_id'], $args['year']));
		$view['weeks_drafts'] = LearningSummaryHelper::assembleWeeklySummaryStats($Draft->getAllLearningSummaryForAYear($_SESSION['school_id'], $req->getAttribute('user_id'), $args['year']));
		$view['year'] = $args['year'];

		return $this->view->render( $res, 'learningSummaryYearSummary.html', $view );
	})->setName( 'learningSummaryYearSummary' );


	/***************************************************************************
	 * GET '/learningSummary/weekly/create'
	 *
	 * Load a view where user can create new Daily Plan
	 **************************************************************************/
	$this->get( '/weekly/{week}/{year}/create[/{draft_id:[0-9]+}]', function ( $req, $res, $args ) use ( $app ) {
        $Story = new Story($this);
        $Child = new Child($this);
        $School = new School($this);
		$Room = new Room($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Create Learning Summary';
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


		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

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
        
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
		$view['children'] = $Child->getAll($_SESSION['school_id']);

		$Draft = new Draft($this);
		$draft_id = $args['draft_id'];
		
		if ($draft_id != null){
			$goals = $Draft->getLearningSummaryGoals($draft_id);

			foreach ($goals as $goal) {
				$view['formgoals'][] = $goal->goal_id;
			}
		}
		else {
			$draft_id = $Draft->addLearningSummary($_SESSION['school_id'], $args['year'], $args['week'], $req->getAttribute('user_id'));
		}

		$view['formdata'] = $Draft->getLearningSummary($draft_id);
		foreach ($Draft->getLearningSummaryChildren($draft_id) as $child) {
			$view['formdata']->children[] = $child->child_id;
		}
		$view['mode'] = 'create';
		$view['draft_id'] = $draft_id;
		

		return $this->view->render($res, 'learningSummaryCreate.html', $view);
	})->setName( 'createLearningSummary' );


	$this->post('/weekly/{week}/{year}/create/{draft_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
		$LearningSummary = new LearningsSummary($this);
		$School = new School($this);
		$Timeline = new Timeline($this);
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

		if (!$data['children']) {
            $this->logger->info('No child selected.', [ 'user_id' =>  $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'You forgot to select the children involved.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('createLearningSummary',
			['year' => $data['year'],
			'week'=>$data['week']]));
		}

		$learning_summary_id = $LearningSummary->create($_SESSION['school_id'],$req->getAttribute('user_id'), $data);

        if (!$learning_summary_id) {
            $this->logger->warning('Learning summarry failed.');
            $this->flash->addMessage('danger', 'The learning summary could not be created.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('createLearningSummary',
			['year' => $data['year'],
			'week'=>$data['week']]));
        }
		
		if ($data['goals']) {
            foreach ($data['goals'] as $v) {
                $LearningSummary->createGoal($v, $learning_summary_id);
            }
        }

        if ($data['texts']) {
            foreach ($data['texts'] as $text_id => $text) {
                $LearningSummary->createText($text, $text_id, $learning_summary_id);
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
                $LearningSummary->createMedia($media_id, $learning_summary_id);
            }
		}
		
		if ($data['children']) {
            foreach ($data['children'] as $v) {
				$LearningSummary->createChild($v, $learning_summary_id);
				
				$Timeline->create($req->getAttribute('user_id'), $v,
					'summary',
					$learning_summary_id,
					1,
					$data['theme']);
            }
		}
		
		// remove the corrispondent draft
		$Draft = new Draft($this);
		$Draft->deleteLearningSummary($args['draft_id']);
		$Draft->purgeLearningSummaryChildren($args['draft_id']);
		$Draft->purgeLearningSummaryGoals($args['draft_id']);

		$this->flash->addMessage('success', 'Learning Summary created.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('learningSummarySummary',
			['year' => $args['year'], 'week' => $args['week']] ));
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
			case 'name_theme':
				$Draft->editLearningSummaryNameTheme( $draftId, $data['value']);
				break;

			case 'child':
				if($data['is_checked'])
					$Draft->associateLearningSummary($draftId, $data['child_id']);
				else{
					$Draft->deassociateLearningSummary($draftId, $data['child_id']);
				}
				break;
			
			case 'media':
				// delete old files
				$prevGroupUrl = $Draft->getLearningSummary($draftId)->group_url;
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
			
				$Draft->editLearningSummaryGroupUrl($draftId, $data['value']);
				break;

			case 'picture_description':
				$Draft->editLearningSummaryPictureDescription( $draftId, $data['value']);
				break;

			case 'goal':
				if($data['is_checked'])
					$Draft->addGoalLearningSummary($draftId, $data['goal_id']);
				else{
					$Draft->removeGoalLearningSummary($draftId, $data['goal_id']);
				}
				break;
		}

		$Draft->updateLearningSummary($draftId);

		return $res->withJson($res_data, 201);
	})->setName('learningSummaryAutosave');


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

		
		// remove the corrispondent draft
		$draft = $Draft->getLearningSummary($data['draft_id']);
		$Draft = new Draft($this);
		$Draft->deleteLearningSummary($data['draft_id']);
		$Draft->purgeLearningSummaryChildren($data['draft_id']);
		$Draft->purgeLearningSummaryGoals($data['draft_id']);
		
		// delete files
		$groupUrl = $draft->group_url;
		if($groupUrl != null)
		{
			$group = $this->uploader->getGroup($groupUrl);
			$files = $group->getFiles();
			foreach ($files as $file) {
				$file->delete();
			}
		}

		$this->flash->addMessage('success', 'Learning Summary draft has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('learningSummarySummary', [
			'year' => $draft->year, 'week' => $draft->week
		]));
	})->setName( 'deleteDraftLearningSummary' );


	$this->post('/weekly/delete', function( $req, $res, $args ) use ( $app ){
		$LearningSummary = new LearningsSummary($this);
		$School = new School($this);
		$Timeline = new Timeline($this);

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

		$learning_summary = $LearningSummary->getLearningSummary($data['learning_summary_id']);

		if ($LearningSummary->purge($data['learning_summary_id'])) {
            $this->logger->info('Deleting timeline entry.', [ 'learning_summary_id' => $data['learning_summary_id'] ]);

            $Timeline->purge('summary', $data['learning_summary_id']);
        }

        $this->logger->info('Learning Summary deleted.', ['user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Learning summary have been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('learningSummarySummary', 
			['year' => $learning_summary->year, 'week' => $learning_summary->week ]));
	})->setName( 'deleteLearningSummary' );


	$this->get('/weekly/{learning_summary_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Story = new Story($this);
		$School = new School($this);
		$LearningSummary = new LearningsSummary($this);
		$Child = new Child($this);
		$Room = new Room($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Edit Learning summary';
		$view['learning_summary_id'] = $args['learning_summary_id'];
		
		$view['formdata'] = $LearningSummary->getLearningSummary($args['learning_summary_id']);

		$startEndDates = getStartAndEndDate($view['formdata']->week, $view['formdata']->year);
		$view['year'] = $view['formdata']->year;
		$view['week'] = $view['formdata']->week;
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		if ($view['formdata']->user_id != $req->getAttribute('user_id')) {
            $this->logger->notice('Story::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('storyDetails', [ 'story_id' => $args['story_id'] ]));
		}
	

        $goals = $LearningSummary->getLearningSummaryGoals($args['learning_summary_id']);
		$texts = $LearningSummary->getLearningSummaryTexts($args['learning_summary_id']);

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


		$view['rooms'] = $Room->getAll($_SESSION['school_id']);
		$view['children'] = $Child->getAll($_SESSION['school_id']);
		
		foreach ($LearningSummary->getChildren($args['learning_summary_id']) as $child) {
            $view['formdata']->children[] = $child->child_id;
		}
		
		$view['mode'] = 'edit';

		return $this->view->render($res, 'learningSummaryCreate.html', $view);
	})->setName( 'editLearningSummary' );



	/***************************************************************************
	 * POST '/learningSummary/weekly/{weekly_plan_id}/edit'
	 *
	 * Save data for an edited Weekly Plan
	 **************************************************************************/
	$this->post('/weekly/{learning_summary_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$School = new School($this);
		$LearningSummary = new LearningsSummary($this);
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

		$LearningSummary->setDetails($args['learning_summary_id'], $data);

		$LearningSummary->purgeGoals($args['learning_summary_id']);
        $LearningSummary->purgeTexts($args['learning_summary_id']);

        if ($data['goals']) {
            foreach ($data['goals'] as $v) {
                $LearningSummary->createGoal($v, $args['learning_summary_id']);
            }
        }

        if ($data['texts']) {
            foreach ($data['texts'] as $text_id => $text) {
                $LearningSummary->createText($text, $text_id, $args['learning_summary_id']);
            }
		}
		
		if ($data['media']) {
            $LearningSummary->purgeMedias($args['learning_summary_id']);

            $this->logger->debug('Media files found.', [ 'group' => $data['media'] ]);

            $group = $this->uploader->getGroup($data['media']);
            $files = $group->getFiles();

            foreach ($files as $file) {
            	// TODO: See if this is necessary, or if we can just use the same image processing as for thumbnails
                $url_full = $file->resize(1600)->getUrl();

                $resized_full = $this->uploader->createLocalCopy($url_full);
                $resized_full->store();

	            $resized_thumbnail = $url_full . '-/resize/400x/';

                $file->delete();

                $this->logger->debug('Saved media.', [ 'learning_summary_id' => $args['learning_summary_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail ]);

                $media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail, $resized_full->data['mime_type']);
                $LearningSummary->createMedia($media_id, $args['learning_summary_id']);
            }
        }

		$this->flash->addMessage('success', 'Weekly plan updated.');

		

		return $res->withStatus(302)->withRedirect($this->router->pathFor('learningSummarySummary',
			['week' => $data['week'],'year' => $data['year']] ));
	});

	/***************************************************************************
	 * GET '/learningSummary/weekly/{week}/{year}'
	 *
	 * View plans associated to a specific week of the year
	 **************************************************************************/
	$this->get( '/weekly/{week}/{year}', function ( $req, $res, $args ) use ( $app ) {
		$User = new User($this);
		$LearningSummary = new LearningsSummary($this);
		$Draft = new Draft($this);

		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Learning Summary';

		$startEndDates = getStartAndEndDate($args['week'], $args['year']);
		$view['year'] = $args['year'];
		$view['week'] = $args['week'];
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));

		$view['learningssummary'] = $LearningSummary->getAllLearningSummaryForAWeek(
			$_SESSION['school_id'],
			$args['year'],
			$args['week']);

		// Grab children
		foreach($view['learningssummary'] as $key => $learningsummary) {

			$view['learningssummary'][$key]->children = $LearningSummary->getChildren($learningsummary->learning_summary_id);
			$view['learningssummary'][$key]->medias = $LearningSummary->getMedias($learningsummary->learning_summary_id);
			$view['learningssummary'][$key]->user = $User->getOne($learningsummary->user_id);
		}

		// get weekly plans drafts
		$view['drafts'] = $Draft->getAllLearningSummaryForAWeek($_SESSION['school_id'], $req->getAttribute("user_id"), $args['year'], $args['week']);
		foreach($view['drafts'] as $key => $draft) {
			$view['drafts'][$key]->children = $Draft->getLearningSummaryChildren($draft->draft_learning_summary_id);
		}

		return $this->view->render( $res, 'learningSummarySummary.html', $view );
	})->setName( 'learningSummarySummary' );
});