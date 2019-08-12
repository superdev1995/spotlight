<?php
/**
 * Risk Assessment
 * Julien TESSIE (julien.tessie@gmail.com)
 * 2018-02-15: Init based on Daily Plan
 */

$app->group('/risks', function() use($app) {
	/***************************************************************************
	 * GET ''
	 *
	 * View summary page where user can select date to display specific Risk
	 *  Assessment
	 **************************************************************************/
	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Risk Assessments';
		
		$Assessment = new RiskAssessment($this);

		if(!empty($req->getQueryParam('riskAssessment_date')))
			$date = $req->getQueryParam('riskAssessment_date');
		else
			$date = date("Y-m-d");
		
		$view['request_date'] = $date;
		$view['assessments'] = $Assessment->getAllAssessments($_SESSION['school_id'], 
			$req->getAttribute('user_id'), $date, $req->getAttribute('user')->user_type);

		return $this->view->render( $res, 'riskAssessment.html', $view );
	})->setName( 'riskAssessments' );

	/***************************************************************************
	 * GET '/create'
	 *
	 * Add a new Risk Assessment
	 **************************************************************************/
	
	$this->get( '/create', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Create Risk Assessment';

		$view['group_items'] = [
			'instructions' => 'Instructions',
			'risks' => 'Risks',
			'minimise' => 'Minimise',
			'review' => 'Review',
			'share' => 'Share options'
		];
		
		$view['shares'] = [
			'teachers'	=> 'Share with all teachers',
			'parents'	=> 'Share with parents'
		];
		
		return $this->view->render($res, 'riskAssessmentCreate.html', $view);
	})->setName( 'createRiskAssessment' );


	/***************************************************************************
	 * GET '/{assessment_id}'
	 *
	 * Load a view where user can see the Risk Assessment
	 **************************************************************************/
	$this->get( '/{assessment_id}', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Risk Assessment';

		
		$view['group_items'] = [
			'instructions' => 'Instructions',
			'risks' => 'Risks',
			'minimise' => 'Minimise',
			'review' => 'Review',
			'share' => 'Share options'
		];
		
		$Assessment = new RiskAssessment($this);
		
		$view['assessment'] = $Assessment->getAssessment($args['assessment_id']);
		if ($view['assessment']->deleted == true) {
			$this->logger->notice('Risk Assessment::getEdit on deleted Element.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id'), 'risk_assessment_id' => $args['assessment_id']]);
			$this->flash->addMessage('danger', 'This assessment was deleted.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('riskAssessments'));
		}
		
		$view['assessment']->risks = $Assessment->getAllRisks($args['assessment_id']);
		$view['riskscount'] = $Assessment->getAllRisksCount($args['assessment_id']);
		
		if (($view['assessment']->school_fk != $_SESSION['school_id'])
			||(($req->getAttribute('user')->user_type=='T')&&($view['assessment']->user_fk != $req->getAttribute('user_id'))&&($view['assessment']->shareteachers == false))
			||(($req->getAttribute('user')->user_type!='T')&&($view['assessment']->shareparents == false))
		) {
			$this->logger->notice('Risk Assessment::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('riskAssessments'));
		}

		$view['formdata'] = $view['assessment'];
		
		
		return $this->view->render($res, 'riskAssessmentDetails.html', $view);
	})->setName( 'riskAssessmentDetails' );
	
	/***************************************************************************
	 * GET '/edit/{assessment_id}'
	 *
	 * Load a view where user can see the Risk Assessment
	 **************************************************************************/
	$this->get( '/edit/{assessment_id}', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Risk Assessment';
		
		$view['group_items'] = [
			'instructions' => 'Instructions',
			'risks' => 'Risks',
			'minimise' => 'Minimise',
			'review' => 'Review',
		];
		
		$Assessment = new RiskAssessment($this);
		
		$view['assessment'] = $Assessment->getAssessment($args['assessment_id']);
		if ($view['assessment']->deleted == true) {
			$this->logger->notice('Risk Assessment::getEdit on deleted Element.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id'), 'risk_assessment_id' => $args['assessment_id']]);
			$this->flash->addMessage('danger', 'This assessment was deleted.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('riskAssessments'));
		}
		
		$view['assessment']->risks = $Assessment->getAllRisks($args['assessment_id']);
		$view['riskscount'] = $Assessment->getAllRisksCount($args['assessment_id']);
		
		if($req->getAttribute('user_id')==$view['assessment']->user_fk)
			$view['group_items']['share'] = 'Share Options';
		
		
		if (($view['assessment']->school_fk != $_SESSION['school_id'])
			||(($view['assessment']->user_fk != $req->getAttribute('user_id'))&&($view['assessment']->shareteachers == false))) {
			$this->logger->notice('Risk Assessment::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('riskAssessments'));
		}

		$view['formdata'] = $view['assessment'];
		
		return $this->view->render($res, 'riskAssessmentCreate.html', $view);
	})->setName( 'editRiskAssessment' );
	
	
	/***************************************************************************
	 * POST '/create'
	 *
	 * Save data for a new Risk Assessment
	 **************************************************************************/
	$this->post('/edit/{assessment_id}', function( $req, $res, $args ) use ( $app ){
		$Assessment = new RiskAssessment($this);
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
	
		// get checkbox value
		$shareParents = 0;
		$shareTeachers = 0;
		if( isset($req_body['share_teachers']) )
			$shareTeachers = 1;
		if( isset($req_body['share_parents']) )
			$shareParents = 1;
		
		// check if the share options is newly selected
		$bMailTeachers = false;
		$bMailParents = false;
		if(($shareTeachers==1)||($shareParents==1)){
			$oldAssessment = $Assessment->getAssessment($req_body['riskAssessment_id']);
			if(($shareTeachers==1)&&($oldAssessment->shareteachers==0))
				$bMailTeachers = true;
			if(($shareParents==1)&&($oldAssessment->shareparents==0))
				$bMailParents = true;
				
		}
		
		$Assessment->updateAssessment( $req_body['riskAssessment_id'],
			trim($req_body['riskAssessment_name']),
				$req_body['riskAssessment_date'],
				$req_body['minimise'],
				$req_body['review'],
				$shareTeachers,
				$shareParents);

		// get risks to check if some have to be deleted
		$old_risks = $Assessment->getAllRisks($req_body['riskAssessment_id']);
		$risksToDelete = array();
		foreach( $old_risks as $old_risk){
			$risksToDelete[] = $old_risk->risk_id;
		}

		// Add or update risks for the assesment
		if(!empty($req_body['risks'])){
			foreach($req_body['risks'] as $risk){
				if( $risk['risk_id']==="" )
					$Assessment->addRisk(
						$req_body['riskAssessment_id'],
						$risk['risk_identified'], 
						$risk['risk_people'],
		    	    	$risk['risk_rating'],
    		    		$risk['risk_actions'],
        				$risk['risk_further_actions'],
		        		$risk['risk_date'],
    		      		$risk['risk_rating_after']
    		      	);
    	      	else {
    	      		$Assessment->updateRisk(
	    	      		$risk['risk_id'],
						$risk['risk_identified'], 
						$risk['risk_people'],
		    	    	$risk['risk_rating'],
    		    		$risk['risk_actions'],
        				$risk['risk_further_actions'],
		        		$risk['risk_date'],
    		      		$risk['risk_rating_after']
    		      	);
    	    
    	      		// delete this id from risksToDelete
	    	      	foreach($risksToDelete as $index => $riskToDelete){
    		      		if($riskToDelete == $risk['risk_id']){
	    		      		unset($risksToDelete[$index]);
	    		      		break;
	    	    	  	}
    	      		}
    	    	}
			}
		}
		
		foreach($risksToDelete as $risk){
			$Assessment->deleteRisk($risk);
		}
		
		// send Teachers notification
		if($bMailTeachers){
			foreach($School->getUsers($_SESSION['school_id']) as $schoolUser){
				$this->mailer->send('riskNotify.html', [
                   'to' => $schoolUser->user_email,
                   'subject' => 'A new risk assessment has been created in your school',
                   'first_name' => $schoolUser->user_first_name,
                   'user' => $req->getAttribute('user'),
                   'assessment_id' => $assessment_id,
               ]);

               $this->logger->info('Notification to teachers sent.', [ 'email' => $schoolUser->user_email ]);				
			}	
		}	
		
		// send Parents notification
		if($bMailParents){
			$Child = new Child($this);
			foreach($Child->getAll($_SESSION['school_id']) as $childUser){
				foreach($Child->getActiveParents($childUser->child_id) as $parents){
					foreach($parents as $parent){
						$this->mailer->send('riskNotify.html', [
	    	               'to' => $parent->user_email,
    	    	           'subject' => 'A new risk assessment has been created in your school',
		                   'first_name' => $parent->user_first_name,
        		           'user' => $req->getAttribute('user'),
                		   'assessment_id' => $assessment_id,
               			]);

               			$this->logger->info('Notification to parents sent.', [ 'email' => $parent->user_email ]);				
               		}
				}
			}	
		}	
		
		return $res->withStatus(302)->withRedirect($this->router->pathFor('editRiskAssessment', array('assessment_id' =>$req_body['riskAssessment_id'])));
			
	});
	
	/***************************************************************************
	 * POST '/create'
	 *
	 * Save data for a new Risk Assessment
	 **************************************************************************/
	$this->post('/create', function( $req, $res, $args ) use ( $app ){
		$Assessment = new RiskAssessment($this);
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
	
		// get checkbox value
		$shareParents = 0;
		$shareTeachers = 0;
		if( isset($req_body['share_teachers']) )
			$shareTeachers = 1;
		if( isset($req_body['share_parents']) )
			$shareParents = 1;

		// if Assessment already exists, do nothing, otherwise create it.
		if( $req_body['riskAssessment_id']==="" )
			$assessment_id = $Assessment->addAssessment( $_SESSION['school_id'],
				$req->getAttribute('user_id'),
				$req_body['riskAssessment_name'],
				$req_body['riskAssessment_date'],
				$req_body['minimise'],
				$req_body['review'],
				$shareTeachers,
				$shareParents);
		else
			$assessment_id = $Assessment->update( $req_body['riskAssessment_id'],
				$req_body['riskAssessment_name'],
				$req_body['riskAssessment_date'],
				$req_body['minimise'],
				$req_body['review'],
				$shareTeachers,
				$shareParents);

		// Add children when there are some.
		foreach($req_body['risks'] as $risk){
			$Assessment->addRisk(
				$assessment_id,
				$risk['risk_identified'], 
				$risk['risk_people'],
	        	$risk['risk_rating'],
   		    	$risk['risk_actions'],
       			$risk['risk_further_actions'],
        		$risk['risk_date'],
   	      		$risk['risk_rating_after']
   	      	);
		}
		
		// send Teachers notification
		if($shareTeachers==1){
			foreach($School->getUsers($_SESSION['school_id']) as $schoolUser){
				$this->mailer->send('riskNotify.html', [
                   'to' => $schoolUser->user_email,
                   'subject' => 'A new risk assessment has been created in your school',
                   'first_name' => $schoolUser->user_first_name,
                   'user' => $req->getAttribute('user'),
                   'assessment_id' => $assessment_id,
               ]);

               $this->logger->info('Notification to teachers sent.', [ 'email' => $schoolUser->user_email ]);				
			}	
		}	
		
		// send Parents notification
		if($shareParents){
			$Child = new Child($this);
			foreach($Child->getAll($_SESSION['school_id']) as $childUser){
				foreach($Child->getActiveParents($childUser->child_id) as $parents){
					foreach($parents as $parent){
						$this->mailer->send('riskNotify.html', [
	    	               'to' => $parent->user_email,
    	    	           'subject' => 'A new risk assessment has been created in your school',
		                   'first_name' => $parent->user_first_name,
        		           'user' => $req->getAttribute('user'),
                		   'assessment_id' => $assessment_id,
               			]);

               			$this->logger->info('Notification to Parents sent.', [ 'email' => $parent->user_email ]);				
               		}
				}
			}	
		}	
		
		return $res->withStatus(302)->withRedirect($this->router->pathFor('riskAssessments'));
	});



	/***************************************************************************
	 * POST 'dailyPlan/delete'
	 *
	 * Delete Daily Plan
	 **************************************************************************/
	$this->post('/delete', function( $req, $res, $args ) use ( $app ){
		$Assessment = new RiskAssessment($this);
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

		$Assessment->deleteAssessment($req_body['risk_assessment_id']);
		
		$this->flash->addMessage('success', 'Risk Assessment has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('riskAssessments'));
	})->setName( 'deleteRiskAssessment' );

});