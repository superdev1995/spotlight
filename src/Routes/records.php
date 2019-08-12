<?php

function getStartAndEndDate($week, $year) {
    $dto = new DateTime();

    $dto->setISODate($year, $week);
    $return[0] = $dto->format('Y-m-d');

    $dto->modify('+6 days');
    $return[1] = $dto->format('Y-m-d');

    return $return;
}

$app->group('/records', function() use($app) {
    /***************************************************************************
     * GET 'records'
     *
     * View record
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Record = new Record($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Daily Records';

        $view['types'] = [
            'note' => 'General Comment',
            'mood' => 'Disposition',
            'meal' => 'Meal',
            'nap' => 'Nap',
            'toilet' => 'Bathroom Break',
            'diaper' => 'Diaper Change',
            'medication' => 'Medication',
        ];

        for ($i = 1; $i <= 52; $i++) {
            $view['weeks'][$i] = getStartAndEndDate($i, date('Y'));
        }

        $view['current_week'] = isset($_GET['week']) ? (int)$_GET['week'] : date('W');

        if ($req->getAttribute('user')->user_type == 'T') {
            $records = $Record->getAllByWeek($_SESSION['school_id'], $view['current_week']);
        } else {
            $records = $Record->getAllParent($_GET['child_id'], $view['current_week']);
        }

        foreach ($records as $record) {
            $view['dates'][$record->record_date][$record->record_time][$record->record_id] = [
                'record_public' => $record->record_public,
                'record_date' => $record->record_date,
                'record_time' => $record->record_time,
                'record_type' => $record->record_type,
                'record_comment' => $record->record_comment,
                'record_medias' => $Record->getMedias($record->record_id),
                'record_children' => $Record->getChildren($record->record_id),
                'record_user' => $User->getOne($record->user_id),
                'created_at' => $record->created_at
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

        $Draft = new Draft($this);
        $view['drafts'] = $Draft->getRecordBatches($_SESSION['school_id'], $req->getAttribute('user_id'));
        foreach($view['drafts'] as $key => $draft){
            $view['drafts'][$key]->children = $Draft->getRecordChildren($draft->draft_record_batch_id);
        }

        return $this->view->render($res, 'record.html', $view);
    })->setName('record');

    /***************************************************************************
     * GET 'records/create'
     *
     * View record create form
     **************************************************************************/
    $this->get('/create[/{draft_id}]', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Record = new Record($this);
        $Draft = new Draft($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Add Daily Records';

        if(isset($this->flash->getMessages()['formdata'][0])){
            $view['formdata'] = $this->flash->getMessages()['formdata'][0];
            unset($view['flash']['formdata']);
        }
        
        
        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }        

        $view['types'] = [
            'note' => 'General Comment',
            'mood' => 'Disposition',
            'meal' => 'Meal',
            'nap' => 'Nap',
            'toilet' => 'Bathroom Break',
            'diaper' => 'Diaper Change',
            'medication' => 'Medication',
        ];

        $view['moods'] = [
            'happy' => 'happy',
            'energetic' => 'energetic',
            'giggly' => 'giggly',
            'cuddly' => 'cuddly',
            'tired' => 'tired',
            'quiet' => 'quiet',
        ];

        $foods = Food::FoodList();
        $records = $Record->getAll($_SESSION['school_id']);

        foreach ($records as $record) {
            foreach ($Record->getParams($record->record_id) as $param) {
                if($param->param_id=="food"){
                    $choice_food=explode(",",$param->param_value);
                    foreach($choice_food as $id_food){
                        if(!in_array($id_food,$foods)){
                            $name_food=str_replace("_"," ",$id_food);
                            $foods[$id_food]=$name_food;
                        }
                    }
                }
            }
        }

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        $view['date'] = date("Y-m-d");
        $view['time'] = date("H:i");

		$draft_id = $args['draft_id'];
		
		if ($draft_id == null){
            $draft_id = $Draft->addRecordBatch($_SESSION['school_id'], $req->getAttribute('user_id'), 1);
            foreach($view['types'] as $type_id => $type_name){
                $draft_record_id = $Draft->addRecord($draft_id, $type_id);
                switch ($type_id) {
                    case "mood":
                        $Draft->addRecordParam($draft_record_id, "mood");
                        break;
                    case "meal":
                        $Draft->addRecordParam($draft_record_id, "amount");
                        $Draft->addRecordParam($draft_record_id, "food");
                        $Draft->addRecordParam($draft_record_id, "meal");
                        break;
                    case "nap":
                        $Draft->addRecordParam($draft_record_id, "end");
                        break;
                    case "diaper":
                        $Draft->addRecordParam($draft_record_id, "condition");
                        break;
                    case "medication":
                        $Draft->addRecordParam($draft_record_id, "amount");
                        $Draft->addRecordParam($draft_record_id, "unit");
                        $Draft->addRecordParam($draft_record_id, "name");
                        break;
                }
            }

            if(isset($_GET['child_id'])){
                $Draft->associateRecordBatch($draft_id, $_GET['child_id']);
            }
        }
        
        $view['formdata'] = $Draft->getRecordBatch($draft_id);

        foreach($Draft->getRecordsInBatch($draft_id) as $record){
            $params = array();
            foreach($Draft->getRecordParams($record->draft_record_id) as $param){
                $param_id = $param->param_id;
                $params[$param_id] = $param->param_value;
            }
            switch ($record->record_type) {
                case "mood":
                    $view['formdata']->mood_comment = $record->record_comment;
                    $view['formdata']->mood_media = $record->group_url;
                    $view['formdata']->mood = $params["mood"];
                    $moods=explode(",", $params["mood"]);
                    $moods=str_replace(' ', '', $moods);
                    foreach ($moods as $mood) {
                        if(!in_array($mood, $view['moods'])){
                            $view['formdata']->mood_other = $mood;
                        }
                    }
                    break;
                case "note":
                    $view['formdata']->note_comment = $record->record_comment;
                    $view['formdata']->note_media = $record->group_url;
                    break;
                case "meal":
                    $view['formdata']->meal_comment = $record->record_comment;
                    $view['formdata']->meal_media = $record->group_url;
                    $view['formdata']->meal_time = $record->record_time;
                    $view['formdata']->meal_amount = $params["amount"];
                    if($params["food"] != ""){
                        $view['formdata']->meal_food = explode(",", $params["food"]);
                        foreach($view['formdata']->meal_food as $id_food){
                            if(!in_array($id_food,$foods)){
                                $name_food=str_replace("_", " ", $id_food);
                                $foods[$id_food] = $name_food;
                            }
                        }
                    }
                    $view['formdata']->meal = $params["meal"];
                    break;
                case "nap":
                    $view['formdata']->nap_comment = $record->record_comment;
                    $view['formdata']->nap_media = $record->group_url;
                    $view['formdata']->start_nap = $record->record_time;
                    $view['formdata']->end_nap = $params["end"];
                    break;
                case "toilet":
                    $view['formdata']->toilet_comment = $record->record_comment;
                    $view['formdata']->toilet_media = $record->group_url;
                    $view['formdata']->toilet_time = $record->record_time;
                    break;
                case "diaper":
                    $view['formdata']->diaper_comment = $record->record_comment;
                    $view['formdata']->diaper_media = $record->group_url;
                    $view['formdata']->diaper_time = $record->record_time;
                    $view['formdata']->diaper_condition = $params["condition"];
                    break;
                case "medication":
                    $view['formdata']->medication_comment = $record->record_comment;
                    $view['formdata']->medication_media = $record->group_url;   
                    $view['formdata']->start_medication = $record->record_time;
                    $view['formdata']->medication_amount = $params["amount"];
                    $view['formdata']->medication_unit = $params["unit"];
                    $view['formdata']->medication_name = $params["name"];
                    break;
            }
        }
        foreach($Draft->getRecordChildren($draft_id) as $child)
            $view['formdata']->children[] = $child->child_id;

        $view['foods'] = $foods;
		$view['mode'] = 'create';
		$view['draft_id'] = $draft_id;

        return $this->view->render($res, 'recordCreate.html', $view);
    })->setName('recordCreate');

    /***************************************************************************
     * POST 'records/create'
     *
     * save record create form
     **************************************************************************/
    $this->post('/create/{draft_id}', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.',
	            ['school_id' => $_SESSION['school_id'],
	             'user_id' => $req->getAttribute('user_id')]
            );
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if (!$data['record_date']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withRedirect($this->router->pathFor('recordCreate', [ 'draft_id' => $args['draft_id'] ]));
        }

        if (!$data['children']) {
            $this->logger->info('No child selected.', [ 'user_id' =>  $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'You forgot to select the children involved.');
            $this->flash->addMessage('formdata', $data);
            return $res->withStatus(302)->withRedirect($this->router->pathFor('recordCreate', [ 'draft_id' => $args['draft_id'] ]));
        }

	    $data['record_public'] = $data['record_public'] ? 1 : 0;

        $mrs = new MultipleRecordSaver();
		$mrs->setParams($_SESSION['school_id'],
			$req->getAttribute('user_id'),
			$data,
			$data['record_public']);
		$mrs->createRecords($this);

        foreach ($data['children'] as $child_id) {


            if ($data['record_public']) {
                $child = $Child->getOne($child_id);

                foreach ($Child->getParents($child_id) as $parent) {
                    if (!$parent->user_notify_record) {
                        continue;
                    }

                    $this->mailer->send('recordNotify.html', [
                        'to' => $parent->user_email,
                        'subject' => 'A new child record has been created for your child',
                        'first_name' => $parent->user_first_name,
                        'user' => $req->getAttribute('user'),
                        'child' => $child,
                    ]);

                    $this->logger->info('Notification sent.', [ 'email' => $parent->user_email ]);
                }
            }
        }

        // Delete corrispondent draft
        $Draft = new Draft($this);
        foreach($Draft->getRecordsInBatch($args['draft_id']) as $record){
            $Draft->purgeRecordParams($record->draft_record_id);
        }
		$Draft->deleteRecordBatch($args['draft_id']);
		$Draft->purgeRecordsInBatch($args['draft_id']);
		$Draft->purgeRecordChildren($args['draft_id']);

        $this->logger->info('Records saved.', ['user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Daily records have been saved.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('record'));
    });

    $this->post('/draft/autosave/{draft_id:[0-9]+}', function( $req, $res, $args ) use ( $app ){
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
            case 'record_date':
				$Draft->editRecordBatchDate( $draftId, $data['value'] );
                break;
                
			case 'public':
				$Draft->editRecordBatchPublic( $draftId, $data['is_checked'] );
                break;
                
            case 'child':
				if($data['is_checked'])
					$Draft->associateRecordBatch($draftId, $data['child_id']);
				else{
					$Draft->deassociateRecordBatch($draftId, $data['child_id']);
				}
				break;
			
			case 'media':
				// delete old files
				$prevGroupUrl = $Draft->getRecordInBatch($draftId, $data['type'])->group_url;
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
			
				$Draft->editRecordGroupUrl($draftId, $data['type'], $data['value']);
				break;

            case 'comment':
                $Draft->editRecordComment($draftId, $data['type'], $data['value']);
                break;

            case 'record_time':
                $Draft->editRecordTime($draftId, $data['type'], $data['value']);
                break;

            case 'param':
                $recordId = $Draft->getRecordInBatch($draftId, $data['type'])->draft_record_id;
                $Draft->editRecordParam($recordId, $data['param_id'], $data['value']);
                break;
        }
        
        $Draft->updateRecordBatch($draftId);

		return $res->withJson($res_data, 201);
	})->setName('recordAutosave');

	/***************************************************************************
	 * POST 'plans/monthly/draft/delete'
	 *
	 * Delete Monthly Plan Draft
	 **************************************************************************/
	$this->post('/draft/delete', function( $req, $res, $args ) use ( $app ){
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

		$Draft->deleteRecordBatch($data['draft_id']);
        $Draft->purgeRecordChildren($data['draft_id']);
        
        foreach($Draft->getRecordsInBatch($data['draft_id']) as $record){
            // delete files
            $groupUrl = $record->group_url;
            if($groupUrl != null)
            {
                $group = $this->uploader->getGroup($groupUrl);
                $files = $group->getFiles();
                foreach ($files as $file) {
                    $file->delete();
                }
            }
            $Draft->purgeRecordParams($record->draft_record_id);
        }

        $Draft->purgeRecordsInBatch($data['draft_id']);
		
		$this->flash->addMessage('success', 'Record draft has been deleted.');

		return $res->withStatus(302)->withRedirect($this->router->pathFor('record'));
	})->setName( 'deleteDraftRecordBatch' );

    /***************************************************************************
     * POST 'records/delete'
     *
     * Delete record 
     **************************************************************************/
    $this->post('/delete', function($req, $res, $args) use($app) {
        $Record = new Record($this);
        $School = new School($this);
        $Timeline = new Timeline($this);

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

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('record'));
        }

        if ($Record->purge($data['record_id'])) {
            $this->logger->info('Deleting timeline entry.', [ 'record_id' => $data['record_id'] ]);

            $Timeline->purge('record', $data['record_id']);
        }

        $this->logger->info('Records deleted.', ['user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Daily records have been deleted.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('record'));
    })->setName('recordDelete');

    /***************************************************************************
     * GET 'records/:record_id/edit'
     *
     * View record edit form
     **************************************************************************/
    $this->get('/{record_id}/edit', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Media = new Media($this);
        $Record = new Record($this);
        $Room = new Room($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Edit Daily Records';

        $view['record_id'] = $args['record_id'];
        $view['formdata'] = $Record->getOne($args['record_id']);
        $view['mode'] = 'edit';

        if ($view['formdata']->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Record::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('record'));
        }

        $view['types'] = [
            'note' => 'General Comment',
            'mood' => 'Disposition',
            'meal' => 'Meal',
            'nap' => 'Nap',
            'toilet' => 'Bathroom Break',
            'diaper' => 'Diaper Change',
            'medication' => 'Medication',
        ];

        $view['moods'] = [
            'happy' => 'happy',
            'energetic' => 'energetic',
            'giggly' => 'giggly',
            'cuddly' => 'cuddly',
            'tired' => 'tired',
            'quiet' => 'quiet',
        ];

        $params = array();
        foreach($Record->getParams($view['formdata']->record_id) as $param){
            $params[$param->param_id] = $param->param_value;
        }
        switch ($view['formdata']->record_type) {
            case "mood":
                $view['formdata']->mood_comment = $view['formdata']->record_comment;
                $view['formdata']->mood = $params["mood"];
                $moods=explode(",", $params["mood"]);
                $moods=str_replace(' ', '', $moods);
                foreach ($moods as $mood) {
                    if(!in_array($mood, $view['moods'])){
                        $view['formdata']->mood_other = $mood;
                    }
                }
                break;
            case "note":
                $view['formdata']->note_comment = $view['formdata']->record_comment;
                break;
            case "meal":
                $view['formdata']->meal_comment = $view['formdata']->record_comment;
                $view['formdata']->meal_time = $view['formdata']->record_time;
                $view['formdata']->meal_amount = $params["amount"];
                $view['formdata']->meal_food = explode(",", $params["food"]);
                $view['formdata']->meal = $params["meal"];
                break;
            case "nap":
                $view['formdata']->nap_comment = $view['formdata']->record_comment;
                $view['formdata']->start_nap = $view['formdata']->record_time;
                $view['formdata']->end_nap = $params["end"];
                break;
            case "toilet":
                $view['formdata']->toilet_comment = $view['formdata']->record_comment;
                $view['formdata']->toilet_time = $view['formdata']->record_time;
                break;
            case "diaper":
                $view['formdata']->diaper_comment = $view['formdata']->record_comment;
                $view['formdata']->diaper_time = $view['formdata']->record_time;
                $view['formdata']->diaper_condition = $params["condition"];
                break;
            case "medication":
                $view['formdata']->medication_comment = $view['formdata']->record_comment;
                $view['formdata']->start_medication = $view['formdata']->record_time;
                $view['formdata']->medication_amount = $params["amount"];
                $view['formdata']->medication_unit = $params["unit"];
                $view['formdata']->medication_name = $params["name"];
                break;
        }

// return $res->withJson($view['formdata']->meal_food);
	    /**
	     * ADDITIONS TO DEAL WITH SAVING MULTIPLE RECORDS AT ONCE
	     */
        // $view['formdata']->note_comment = $view['formdata']->record_comment;
	    // 
        // $view['formdata']->nap_comment = $view['formdata']->record_comment;
	    // $view['formdata']->medication_comment = $view['formdata']->record_comment;
	    // $view['formdata']->toilet_comment = $view['formdata']->record_comment;
	    // $view['formdata']->diaper_comment = $view['formdata']->record_comment;
	    // $view['formdata']->meal_comment = $view['formdata']->record_comment;

	    // $view['formdata']->toilet_time = $view['formdata']->record_time;
	    // $view['formdata']->diaper_time = $view['formdata']->record_time;
	    // $view['formdata']->meal_time = $view['formdata']->record_time;

	    // $view['formdata']->meal_amount = $view['formdata']->record_amount;

	    // $view['formdata']->medication_amount = $view['formdata']->record_amount;
	    // $view['formdata']->medication_unit = $view['formdata']->record_unit;
	    // $view['formdata']->medication_name = $view['formdata']->record_name;

	    /*print "<pre>";
	    print_r($view);
	    print "</pre>";
	    die;*/
        /**
         * ADDITIONS TO DEAL WITH SAVING MULTIPLE RECORDS AT ONCE
         */

        

        $foods=Food::FoodList();
        $records = $Record->getAll($_SESSION['school_id']);

        foreach ($records as $record) {
            foreach ($Record->getParams($record->record_id) as $param) {
                if($param->param_id=="food"){
                    $choice_food=explode(",",$param->param_value);
                    foreach($choice_food as $id_food){
                        if(!in_array($id_food,$foods)){
                            $name_food=str_replace("_"," ",$id_food);
                            $foods[$id_food]=$name_food;
                        }
                    }
                }
            }
        }

        $view['foods']=$foods;

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        foreach ($Record->getChildren($view['formdata']->record_id) as $child) {
            $view['formdata']->children[] = $child->child_id;
        }

        return $this->view->render($res, 'recordCreate.html', $view);
    })->setName('recordEdit');

    /***************************************************************************
     * POST 'records/:record_id/edit'
     *
     * Save record edit form
     **************************************************************************/
    $this->post('/{record_id}/edit', function($req, $res, $args) use($app) {
        $Media = new Media($this);
        $Record = new Record($this);
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

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('record'));
        }

        $record = $Record->getOne($args['record_id']);

        if ($record->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Record::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('record'));
        }

        if (!$data['children']) {
            $this->logger->info('No child selected.', [ 'user_id' => $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'You forgot to select the children involved.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('recordCreate'));
        }

        $data['record_public'] = $data['record_public'] ? 1 : 0;

        if( $data['note_comment'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["note_comment"]);
            $media= isset($data['note_media']) ? $data['note_media'] :false;
        }elseif(isset($data['param']['mood'][0])){
            $Record->setDetails($args['record_id'], $data, $data["mood_comment"]);
            if (count($data['param'])) {
                foreach ($data['param'] as $key => $value) {
                    if($key!="food"){
                        if (is_array($value)) {
                            $value = array_filter($value);
                            $value = implode(', ', $value);
                        }
                        $Record->setParam($args['record_id'], $key, $value);
                    }
                    
                }
            }
            $media= isset($data['mood_media']) ? $data['mood_media'] :false;
        }elseif(isset($data['param']['meal_time'])){
            $Record->setDetails($args['record_id'], $data, $data["meal_comment"], $data['param']["meal_time"]);
            $Record->setParam($args['record_id'], 'amount', $data['param']['meal_amount']);
            $Record->setParam($args['record_id'], 'meal', $data['param']['meal']);
            $Record->setParam($args['record_id'], 'food', $data['param']['food']);
            $media= isset($data['meal_media']) ? $data['meal_media'] :false;
        }elseif(isset($data['param']['start_nap'])){
            $Record->setDetails($args['record_id'], $data, $data["nap_comment"],$data['param']["start_nap"]);
            $Record->setParam($args['record_id'], 'end', $data['param']['end_nap']);
            $media= isset($data['nap_media']) ? $data['nap_media'] :false;
        }elseif(isset($data['param']['toilet_time'])){
            $Record->setDetails($args['record_id'], $data, $data["toilet_comment"],$data['param']["toilet_time"]);
            $media= isset($data['toilet_media']) ? $data['toilet_media'] :false;
        }elseif(isset($data['param']['diaper_time'])){
            $Record->setDetails($args['record_id'], $data, $data["diaper_comment"],$data['param']["diaper_time"]);
            $Record->setParam($args['record_id'], 'condition', $data['param']['condition']);
            $media= isset($data['diaper_media']) ? $data['diaper_media'] :false;
        }elseif(isset($data['param']['start_medication'] )){
            $Record->setDetails($args['record_id'], $data, $data["medication_comment"],$data['param']['start_medication']);
            $Record->setParam($args['record_id'], 'name', $data['param']['medication_name']);
			$Record->setParam($args['record_id'], 'amount', $data['param']['medication_amount']);
            $Record->setParam($args['record_id'], 'unit', $data['param']['medication_unit']);
            $media= isset($data['medication_media']) ? $data['medication_media'] :false;
        }

        $Record->purgeChildren($args['record_id']);

        foreach ($data['children'] as $child_id) {
            $Record->createChild($child_id, $args['record_id']);
        }

        if ($media) {
            $Record->purgeMedias($args['record_id']);

            $this->logger->debug('Media files found.', [ 'group' => $media ]);

            $group = $this->uploader->getGroup($media);
            $files = $group->getFiles();

            foreach ($files as $file) {
            	// TODO: See if this is necessary, or if we can just use the same image processing as for thumbnails
                $url_full = $file->resize(1600)->getUrl();

                $resized_full = $this->uploader->createLocalCopy($url_full);
                $resized_full->store();

	            $resized_thumbnail = $url_full . '-/resize/400x/';

                $file->delete();

                $this->logger->debug('Saved media.', [ 'record_id' => $args['record_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail ]);

                $media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail, $resized_full->data['mime_type']);
                $Record->createMedia($media_id, $args['record_id']);
            }
        }

        $this->logger->info('Records updated.', [ 'record_id' => $args['record_id'], 'user_id' => $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'Daily records have been updated.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('record'));
    });
});
