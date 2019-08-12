<?php
/**
 * Fire Safety
 * Julien TESSIE (julien.tessie@gmail.com)
 * 2018-02-24: Init based on Risk Assessment
 */

$app->group('/safety', function() use($app) {
	/***************************************************************************
	 * GET 'fireDuties'
	 *
	 * View User sees 3 tabs they are called owner, manager and deputy.
	 *		User clicks on tabs and fills out the relevant sections.
	 **************************************************************************/
	$this->get( '/fireDuties', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Assigned fire duties';
		
		$view['group_items'] = [
			'owner' => 'Owner',
			'manager' => 'Manager',
			'deputy' => 'Deputy',
		];
		$Safety = new Safety($this);

		$Duties = $Safety->getAllDuties($_SESSION['school_id']);
		foreach( $Duties as $Duty ){
			$view['duties'][$Duty->type] = $Duty;
		}
		//$view['user'] = $req->getAttribute('user');
		return $this->view->render( $res, 'fireDuties.html', $view );
	})->setName( 'fireDuties' );

	/***************************************************************************
	 * GET 'fireDuties'
	 *
	 * View User sees 3 tabs they are called owner, manager and deputy.
	 *		User clicks on tabs and fills out the relevant sections.
	 **************************************************************************/
	$this->get( '/fireDuties/edit', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Assigned fire duties';
		
		$view['group_items'] = [
			'owner' => 'Owner',
			'manager' => 'Manager',
			'deputy' => 'Deputy',
		];
		$Safety = new Safety($this);

		$Duties = $Safety->getAllDuties($_SESSION['school_id']);
		foreach( $Duties as $Duty ){
			$view['duties'][$Duty->type] = $Duty;
		}
		//$view['user'] = $req->getAttribute('user');
		return $this->view->render( $res, 'fireDutiesEdit.html', $view );
	})->setName( 'editFireDuties' );

	
	/***************************************************************************
	 * POST '/fireduties'
	 *
	 * Save data for all fire duties
	 **************************************************************************/
	$this->post('/fireDuties/edit', function( $req, $res, $args ) use ( $app ){
		$Safety = new Safety($this);
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
	
		// Add Duties.
		foreach($req_body['duties'] as $duty){
			$Safety->addDuty(
				$duty['type'],
				$_SESSION['school_id'], 
				$duty['name'],
	        	$duty['phone'],
   		    	$duty['duties'],
       			$duty['comments']
   	      	);
		}
		
		return $res->withStatus(302)->withRedirect($this->router->pathFor('fireDuties'));
	});




	/***************************************************************************
	 * GET ''
	 *
	 * View User sees 3 tabs they are called owner, manager and deputy.
	 *		User clicks on tabs and fills out the relevant sections.
	 **************************************************************************/
	$this->get( '/emergencyInventory', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Inventory of Emergency Lighting Equipment';
		
		$Safety = new Safety($this);

		
		$view['inventory'] = $Safety->getAllInventory($_SESSION['school_id']);

		return $this->view->render( $res, 'emergencyInventory.html', $view );
	})->setName( 'emergencyInventory' );
	
	$this->get( '/emergencyInventory/edit', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Inventory of Emergency Lighting Equipment';
		
		$Safety = new Safety($this);

		$inventory = $Safety->getAllInventory($_SESSION['school_id']);

		foreach( $inventory as $item ){
			$view['inventory'][$item->id] = $item;
		}

		return $this->view->render( $res, 'emergencyInventoryEdit.html', $view );
	})->setName( 'editEmergencyInventory' );
	
	/***************************************************************************
	 * POST '/emergencyInventory'
	 *
	 * Save the inventory
	 **************************************************************************/
	$this->post('/emergencyInventory/edit', function( $req, $res, $args ) use ( $app ){
		$Safety = new Safety($this);
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
	
		
		$old_items = $Safety->getAllInventory($_SESSION['school_id']);
		$itemsToDelete = array();
		foreach( $old_items as $old_item){
			$itemsToDelete[] = $old_item->id;
		}

		if(!empty($req_body['inventory'])){
			foreach($req_body['inventory'] as $item){
				if( $item['id']==="" )
					$Safety->addItem(
						$_SESSION['school_id'],
						$item['location_of_equipment'], 
						$item['number'],
		    	    	$item['type'],
    		    		$item['location']
    		      	);
    	      	else {
    	      		$Safety->updateItem(
	    	      		$item['id'],
	    	      		$item['location_of_equipment'], 
						$item['number'],
		    	    	$item['type'],
    		    		$item['location']
    		      	);
    	    
    	      		foreach($itemsToDelete as $index => $itemToDelete){
    		      		if($itemToDelete == $item['id']){
	    		      		unset($itemsToDelete[$index]);
	    		      		break;
	    	    	  	}
    	      		}
    	    	}
			}
		}
		
		foreach($itemsToDelete as $item){
			$Safety->deleteItem($item);
		}
		
		return $res->withStatus(302)->withRedirect($this->router->pathFor('emergencyInventory'));
	});
	
	
	
	$this->get( '/logBook', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Fire Safety Log Book';
		
		$Safety = new Safety($this);

		
		$view['book'] = $Safety->getAllLogs($_SESSION['school_id']);

		return $this->view->render( $res, 'logBook.html', $view );
	})->setName( 'logBook' );
	
	$this->get( '/logBook/edit', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Fire Safety Log Book';
		
		$Safety = new Safety($this);

		$book = $Safety->getAlllogs($_SESSION['school_id']);

		foreach( $book as $entry ){
			$view['book'][$entry->id] = $entry;
		}

		return $this->view->render( $res, 'logBookEdit.html', $view );
	})->setName( 'editLogBook' );
	
	/***************************************************************************
	 * POST '/emergencyInventory'
	 *
	 * Save the inventory
	 **************************************************************************/
	$this->post('/logBook/edit', function( $req, $res, $args ) use ( $app ){
		$Safety = new Safety($this);
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
	
		
		$old_items = $Safety->getAllLogs($_SESSION['school_id']);
		$itemsToDelete = array();
		foreach( $old_items as $old_item){
			$itemsToDelete[] = $old_item->id;
		}

		
		if(!empty($req_body['book'])){
			foreach($req_body['book'] as $entry){
				if($entry['date']=="")
					$date= null;
				else
					$date=$entry['date'];
				if( $entry['id']==="" )
					$Safety->addLogEntry(
						$_SESSION['school_id'],
						$date, 
						$entry['nature_of_drill'], 
						$entry['persons'], 
						$entry['evacuation_time'], 
						$entry['person_in_charge'], 
						$entry['comments']
    		      	);
    	      	else {
    	      		$Safety->updateLogEntry(
	    	      		$entry['id'],
	    	      		$date, 
						$entry['nature_of_drill'], 
						$entry['persons'], 
						$entry['evacuation_time'], 
						$entry['person_in_charge'], 
						$entry['comments']	
    		      	);
    	    
    	      		foreach($itemsToDelete as $index => $itemToDelete){
    		      		if($itemToDelete == $entry['id']){
	    		      		unset($itemsToDelete[$index]);
	    		      		break;
	    	    	  	}
    	      		}
    	    	}
			}
		}
		
		foreach($itemsToDelete as $item){
			$Safety->deleteLogEntry($item);
		}
		
		return $res->withStatus(302)->withRedirect($this->router->pathFor('logBook'));
	});
	
	
	
	
	
	
	
	$this->get( '/generalRegister', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'General Safety Register';
		
		$Safety = new Safety($this);

		$view['register'] = $Safety->getAllRegister($_SESSION['school_id']);

		return $this->view->render( $res, 'generalRegister.html', $view );
	})->setName( 'generalRegister' );
	
	$this->get( '/generalRegister/edit', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'General Safety Register';
		
		$Safety = new Safety($this);

		$register = $Safety->getAllRegister($_SESSION['school_id']);

		foreach( $register as $entry ){
			$entry->file_name = basename($entry->file_url);
			$view['register'][$entry->id] = $entry;
		}

		$view['group_items'] = [
			'instructions' => 'Instructions',
			'details' => 'Register'
		];

		return $this->view->render( $res, 'generalRegisterEdit.html', $view );
	})->setName( 'editGeneralRegister' );
	
	/***************************************************************************
	 * POST '/emergencyInventory'
	 *
	 * Save the inventory
	 **************************************************************************/
	$this->post('/generalRegister/edit', function( $req, $res, $args ) use ( $app ){
		$Safety = new Safety($this);
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
	
		
		$old_items = $Safety->getAllRegister($_SESSION['school_id']);
		$itemsToDelete = array();
		foreach( $old_items as $old_item){
			$itemsToDelete[] = $old_item->id;
		}

		
		if(!empty($req_body['register'])){
			foreach($req_body['register'] as $entry){
				//if the date is today or before set the entry to completed
				$entry['completed'] = 0;
				//die(var_dump($entry['date_to_be_completed'],date('Y-m-d'), $entry['date_to_be_completed'] <= date('Y-m-d')));
				if($entry['date_to_be_completed'] <= date('Y-m-d')){
					$entry['completed']=1;
				}
					
				if( $entry['id']==="" )
					$Safety->addRegisterEntry(
						$_SESSION['school_id'],
						$entry['date'], 
						$entry['time'], 
						$entry['log_number'], 
						$entry['documented_by'], 
						$entry['drill'], 
						$entry['inspection_of'], 
						$entry['fire'], 
						$entry['fault'], 
						$entry['other'], 
						$entry['action'], 
						$entry['date_to_be_completed'], 
						$entry['completed'],
						$entry['file_url']
    		      	);
    	      	else {
    	      		// check file_url
    	      		if($entry['file_url'] == "")
    	      			if($entry['file_url_hidden']!="")
    	      				$entry['file_url']=$entry['file_url_hidden'];
    	      		
    	      		$Safety->updateRegisterEntry(
	    	      		$entry['id'],
	    	      		$entry['date'], 
						$entry['time'], 
						$entry['log_number'], 
						$entry['documented_by'], 
						$entry['drill'], 
						$entry['inspection_of'], 
						$entry['fire'], 
						$entry['fault'], 
						$entry['other'], 
						$entry['action'], 
						$entry['date_to_be_completed'], 
						$entry['completed'],
						$entry['file_url']
    		      	);
    	    
    	      		foreach($itemsToDelete as $index => $itemToDelete){
    		      		if($itemToDelete == $entry['id']){
	    		      		unset($itemsToDelete[$index]);
	    		      		break;
	    	    	  	}
    	      		}
    	    	}
			}
		}
		
		foreach($itemsToDelete as $item){
			$Safety->deleteRegisterEntry($item);
		}
		
		return $res->withStatus(302)->withRedirect($this->router->pathFor('generalRegister'));
	});
	
	$this->get( '/generalRegister/attachments', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Attachments for General Safety Register';
		
		$Safety = new Safety($this);
		$view['attachments'] = $Safety->getAllAttachments($_SESSION['school_id']);
		foreach( $view['attachments'] as $key => $attachment){
			$view['attachments'][$key]->file_name = basename($view['attachments'][$key]->file_url);
		}

		return $this->view->render( $res, 'safetyAttachments.html', $view );
			
	})->setName( 'safetyAttachments' );
	
	$this->post('/generalRegister/attachments', function($req, $res, $args) use($app) {
        $Safety = new Safety($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $this->flash->addMessage('formdata', $data);

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Insufficient rights.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id'), 'role' => $school_user->role]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('safetyAttachments'));
        }

		// delete file if asked
		$old_items = $Safety->getAllAttachments($_SESSION['school_id']);
		$itemsToDelete = array();
		
		foreach( $data['attachments'] as $attachment){
			foreach( $old_items as $key => $old_item){
				if($old_item->attachment_id == $attachment->attachment_id){
					unset( $old_items[$key] );
					break;
				}
			}		
		}
		
		foreach($old_items as $old_item){
			$Safety->deleteAttachment($old_item);
		}
		
		
        if (!$data['file']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('safetyAttachments'));
        }

        if ($data['file']) {
            $file = $this->uploader->getFile($data['file']);
            $file->store();

            $this->logger->debug('Saving general register document.', [ 'url' => $file->getUrl() ]);

            $data['file_url'] = $file->getUrl();
        	if(!($Safety->createAttachment($_SESSION['school_id'], $data))){
        		$this->logger->error('Safety::create attachment failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
	            $this->flash->addMessage('danger', 'Safety Attachment could not be saved.');
			
            	return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('safetyAttachments'));
            }
        }

        $this->flash->addMessage('success', 'Your attachment has been saved.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('safetyAttachments'));
    });

	$this->post('/{attachment_id}/delete', function($req, $res, $args) use($app) {
		$Safety = new Safety($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $attachment = $Safety->getAttachment($args['attachment_id'], $_SESSION['school_id']);

        /**
         * We want to get the school_id saved in the policy itself to ensure
         * that we check for the user’s permission associated with it.
         */
        $school_user = $School->getUser($attachment->school_fk, $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Insufficient rights.', ['school_id' => $policy->school_id, 'role' => $school_user->role]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('safetyAttachments'));
        }

        if (!$Safety->purgeAttachment($args['attachment_id'],$attachment->school_fk)) {
            $this->logger->error('Safety Attachment::delete failed.', ['attachment_id' => $args['attachment_id'], 'school_id' => $attachment->school_fk]);
            $this->flash->addMessage('danger', 'Attachment could not be deleted.');
        }

        $this->logger->info('Safety deleted.', ['attachment_id' => $args['attachment_id'], 'school_id' => $attachment->school_fk]);
        $this->flash->addMessage('success', 'Your attachment has been deleted.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('safetyAttachments'));
    })->setName('safetyAttachmentDelete');
});