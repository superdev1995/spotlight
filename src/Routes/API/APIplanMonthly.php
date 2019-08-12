<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

use \Carbon\Carbon;

$app->group('/API/plans', function() use($app) {

	/***************************************************************************
	 * GET '/plans/monthly/all/{year}'
	 *
	 * View summary page where user can select date to display specific Monthly
	 *  Plan
	 **************************************************************************/
	$this->get( '/monthly/all/{year}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
		
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
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

		$months_completed = array();
		$months_completed_obj =
			$Plan->getAllMonthlyPlansForAYear($_SESSION['school_id'], $args['year']);
		foreach($months_completed_obj as $plan){
			$months_completed[$plan->id] = $plan->month;
		}

		// TODO: implement
		$view['months_completed'] = $months_completed;
        $view['year'] = $args['year'];
        
        return $res->withJSON($view);

		return $this->view->render( $res, 'monthlyPlanSummary.html', $view );
	})->setName( 'summaryMonthlyPlan' );



	/***************************************************************************
	 * GET '/plans/monthly/create'
	 *
	 * Load a view where user can create new Daily Plan
	 **************************************************************************/
	$this->get( '/monthly/{month}/{year}/create', function ( $req, $res, $args ) use ( $app ) {
		$School = new School($this);
		$Story = new Story($this);
		$Form = new Form($this);


		/**
		 * // TODO: REFACTOR
		 */
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		$school = $School->getOne($_SESSION['school_id']);

		// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
		$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);

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
		/**
		 * // TODO: REFACTOR
		 */

		$view['title'] = "Create Monthly Plans";

		$view['plan'] = new stdClass();
		$view['plan']->month = $args['month'];
		$view['plan']->month_textual = Dates::convertToReadableMonth($view['plan']->month);
		$view['plan']->year = $args['year'];

		return $this->view->render( $res, 'monthlyPlanCreate.html', $view );
	})->setName( 'createMonthlyPlan' );



	/***************************************************************************
	 * POST '/plans/monthly/create'
	 *
	 * Save data for a new Daily Plan
	 **************************************************************************/
	$this->post('/monthly/{month}/{year}/create', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$Media = new Media($this);
		$Child = new Child($this);
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
		$data['children']=$Child->getAll($_SESSION['school_id']);

		$plan_id = $Plan->createMonthlyPlan($data);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoal($v, $plan_id);
			}
		}

		$this->logger->info('Plan created.', [ 'monthly_plan_id' => $plan_id, 'user_id' => $req->getAttribute('user_id') ]);

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

		foreach ($data['children'] as $child_id) {
				
			$Timeline->create($req->getAttribute('user_id'), $child_id->child_id,
				'monthlyPlan',
				$plan_id,
				$data['public'],
				$data['theme']);

			if ($data['public']==1) {
                foreach ($Child->getParents($child_id->child_id) as $parent) {
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

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryMonthlyPlan', ['year' => date('Y')]));
	});



	/***************************************************************************
	 * POST '/plans/monthly/delete'
	 *
	 * Delete Daily Plan
	 **************************************************************************/
	$this->post('/monthly/{monthly_plan_id}/delete', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		$Plan->purgeMonthlyMedias($args['monthly_plan_id']);
		$Plan->purgeMonthlyPlanGoals($args['monthly_plan_id']);
		$Plan->deleteMonthlyPlan($args['monthly_plan_id']);

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryMonthlyPlan', ['year' => date('Y')]));
	})->setName( 'deleteMonthlyPlan' );



	/***************************************************************************
	 * POST '/plans/monthly/{daily_plan_id}/edit'
	 *
	 * Load the page and data for a specific Monthly Plan for editing
	 **************************************************************************/
	$this->get('/monthly/{monthly_plan_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Story = new Story($this);
		$School = new School($this);
		$Plan = new Plan($this);
		$Form = new Form($this);

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		$view['plan'] = $Plan->getMonthlyPlanById($args['monthly_plan_id']);
		$view['plan']->month_textual = Dates::convertToReadableMonth($view['plan']->month);

		$view['medias'] = $Plan->getMonthlyMedias($args['monthly_plan_id']);

		/**
		 * // TODO: REFACTOR
		 */
		$school = $School->getOne($_SESSION['school_id']);

		// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
		$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);

		$categories = $Story->getCategories($school->country_id);

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
		/**
		 * // TODO: REFACTOR
		 */

		return $this->view->render( $res, 'monthlyPlanCreate.html', $view );
	})->setName( 'editMonthlyPlan' );




	/***************************************************************************
	 * POST '/plans/monthly/{monthly_plan_id}/edit'
	 *
	 * Save data for an edited Monthly Plan
	 **************************************************************************/
	$this->post('/monthly/{monthly_plan_id}/edit', function( $req, $res, $args ) use ( $app ){
		$Plan = new Plan($this);
		$Media = new Media($this);
		$Timeline = new Timeline($this);
		$Child = new Child($this);

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		$data = $req->getParsedBody();

		$data['public'] = $data['public'] ? 1 : 0;
		$data['children']=$Child->getAll($_SESSION['school_id']);

		if ($req->getAttribute('csrf_status') === false) {
			$this->logger->error('CSRF failure.');
			$this->flash->addMessage('danger', 'Internal error.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		}

		if($data['public']==1){
			$Timeline->updateVisibility('monthlyPlan',$args['monthly_plan_id']);

			foreach ($data['children'] as $child_id) {
				
				foreach ($Child->getParents($child_id->child_id) as $parent) {
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
		
		$Plan->updateMonthlyPlan($args['monthly_plan_id'], $data);
		$Plan->purgeMonthlyPlanGoals($args['monthly_plan_id']);

		if ($data['goals']) {
			foreach ($data['goals'] as $v) {
				$Plan->createGoal($v, $args['monthly_plan_id']);
			}
		}

		if ($data['media']) {
			$Plan->purgeMonthlyMedias($args['monthly_plan_id']);

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

		$this->logger->info('Plan updated.', [ 'monthly_plan_id' => $args['monthly_plan_id'], 'user_id' => $req->getAttribute('user_id') ]);
		$this->flash->addMessage('success', 'Monthly plan has been updated.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('summaryMonthlyPlan', ['year' => date('Y')]));
	});



	/***************************************************************************
	 * GET '/plans/{month}/{year}'
	 *
	 * View single Monthly Plan
	 **************************************************************************/
	$this->get( '/monthly/{month}/{year}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
		$Child = new Child($this);
		$Story = new Story($this);
		$Form = new Form($this);

		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
		} 

		if ($req->getAttribute('user')->user_type == 'T') {
            $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

            if (!$view['school_user']) {
                $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        } else {
			$allParents = $Child->getAllParentsForSchool($_SESSION['school_id']);
			$isParent = false;
			foreach ($allParents as $parent) {
				if ($parent->user_id == $req->getAttribute('user_id')) {
					$isParent = true;
					break;
				}
			}

			if($isParent === false) {
				$this->logger->notice('Child::getParent failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
				$this->flash->addMessage('danger', 'You don’t have sufficient rights.');
	
				return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
			}
        }

		$view['plan'] = $Plan->getMonthlyPlan($args['year'], $args['month'], $_SESSION['school_id']);

		/**
		 * // TODO: REFACTOR
		 */
		$school = $School->getOne($_SESSION['school_id']);

		// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
		$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);

		$categories = $Story->getCategories($school->country_id);

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
		$view['plan']->medias = $Plan->getMonthlyMedias($view['plan']->monthly_plan_id);

		return $this->view->render( $res, 'monthlyPlanSingle.html', $view );
	})->setName( 'monthlyPlan' );

	/***************************************************************************
	 * GET '/{child_id}/plans/{month}/{year}'
	 *
	 * View single Monthly Plan for parents
	 **************************************************************************/
	$this->get( '/{child_id}/monthly/{month}/{year}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
		$Child = new Child($this);
		$Story = new Story($this);
		$Form = new Form($this);

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

		// TODO: Place the plan_name('monthly_plan_details') into the database somewhere
		$view['country_form'] = $Form->retrieveBlocks('monthly_plan_details', $school->country_id);

		$categories = $Story->getCategories($school->country_id);

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
		$view['plan']->medias = $Plan->getMonthlyMedias($view['plan']->monthly_plan_id);

		return $this->view->render( $res, 'monthlyPlanSingle.html', $view );
	})->setName( 'childMonthlyPlan' );
});