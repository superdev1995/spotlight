<?php

$app->group('/API/records', function() use($app) {
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

        $types = [
            'note' => 'General Comment',
            'mood' => 'Disposition',
            'meal' => 'Meal',
            'nap' => 'Nap',
            'toilet' => 'Bathroom Break',
            'diaper' => 'Diaper Change',
            'medication' => 'Medication',
        ];

        for ($i = 1; $i <= 52; $i++) {
            $weeks[$i] = getStartAndEndDate($i, date('Y'));
        }

        $view['current_week'] = $_GET['week'] ? (int)$_GET['week'] : date('W');

        if ($req->getAttribute('user')->user_type == 'T') {
            $records = $Record->getAllByWeek($_SESSION['school_id'], $view['current_week']);
        } else {
            $records = $Record->getAllParent($_GET['child_id'], $view['current_week']);
        }

        foreach ($records as $record) {
            $dates[$record->record_date][$record->record_time][$record->record_id] = [
                'record_public' => $record->record_public,
                'record_date' => $record->record_date,
                'record_time' => $record->record_time,
                'record_type' => $record->record_type,
                'record_comment' => $record->record_comment,
                'record_medias' => $Record->getMedias($record->record_id),
                'record_children' => $Record->getChildren($record->record_id),
                'record_user' => $User->getOne($record->user_id),
                'created_at' => $record->created_at,
            ];

            foreach ($Record->getParams($record->record_id) as $param) {
                $record_params[$param->param_id] = $param->param_value;
            }

            if ($record_params) {
                foreach ($record_params as $key => $value) {
                    $record_params[$record->record_id][$key] = $value;
                }
            }
        }

        $data=array('records'=>$records,'records_param'=>$record_params,'types'=>$types,'weeks'=>$weeks,'dates'=>$dates);

        return $res->withJson($data);
    })->setName('record');

    /***************************************************************************
     * GET 'records/create'
     *
     * View record create form
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $view['formdata'] = $_GET['child_id'] ? ['children'=>(int)$_GET['child_id']] : '';//$this->flash->getMessages()['formdata'][0];

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

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        $view['date'] = date("Y-m-d");
        $view['time'] = date("H:i");

        $data=array('rooms'=>$view['rooms'],'children'=>$view['children'],'types'=>$view['types'],'moods'=>$view['moods']);

        return $res->withJson($data);
    })->setName('recordCreate');

    /***************************************************************************
     * POST 'records/create'
     *
     * save record create form
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.',
	            ['school_id' => $_SESSION['school_id'],
	             'user_id' => $req->getAttribute('user_id')]
            );
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $this->flash->addMessage('formdata', $data);

        if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $data['record_date'])) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        
        if (!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/", $data["param"]["meal_time"]) && $data["param"]["meal_time"]!="") {
            $this->logger->info('User submitted invalid time.');
            
            return $res->withJson(['error'=>'The time appears invalid in meal. Please write as HH:mm.']);
        }

        if (!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/", $data["param"]["start_nap"]) && $data["param"]["start_nap"]!="") {
            $this->logger->info('User submitted invalid time.');
            
            return $res->withJson(['error'=>'The time appears invalid in nap. Please write as HH:mm.']);
        }

        if (!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/", $data["param"]["toilet_time"]) && $data["param"]["toilet_time"]!="") {
            $this->logger->info('User submitted invalid time.');
            
            return $res->withJson(['error'=>'The time appears invalid in bhthroom break. Please write as HH:mm.']);
        }

        if (!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/", $data["param"]["diaper_time"]) && $data["param"]["diaper_time"]!="") {
            $this->logger->info('User submitted invalid time.');
            
            return $res->withJson(['error'=>'The time appears invalid in diaper change. Please write as HH:mm.']);
        }

        if (!preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/", $data["param"]["start_medication"]) && $data["param"]["start_medication"]!="") {
            $this->logger->info('User submitted invalid time.');
            
            return $res->withJson(['error'=>'The time appears invalid in medication. Please write as HH:mm.']);
        }
        
        
        return $res->withJson(['error'=>'The time appears invalid in medication. Please write as HH:mm.']);

        if (!$data['children']) {
            $this->logger->info('No child selected.', [ 'user_id' =>  $req->getAttribute('user_id') ]);

            return $res->withJson(['error'=>'You forgot to select the children involved.']);
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

        $this->logger->info('Records saved.', ['user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'=>'Daily records have been saved.']);
    });

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

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
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

        $view['record'] = $Record->getOne($args['record_id']);

        if ($view['record']->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Record::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $view['formdata'] = $view['record'];

        foreach ($Record->getParams($view['record']->record_id) as $param) {
            $param_id = 'record_'.$param->param_id;

            $view['formdata']->$param_id = $param->param_value;
        }

        switch ($view['formdata']->record_type) {
            case "mood":
                $view['formdata']->mood_comment = $view['formdata']->record_comment;
                break;
            case "note":
                $view['formdata']->general_comment = $view['formdata']->record_comment;
                break;
            case "meal":
                $view['formdata']->meal_comment = $view['formdata']->record_comment;
                $view['formdata']->meal_time = $view['formdata']->record_time;
                $view['formdata']->meal_amount = $view['formdata']->record_amount;
                break;
            case "nap":
                $view['formdata']->nap_comment = $view['formdata']->record_comment;
                break;
            case "toilet":
                $view['formdata']->toilet_comment = $view['formdata']->record_comment;
                $view['formdata']->toilet_time = $view['formdata']->record_time;
                break;
            case "diaper":
                $view['formdata']->diaper_comment = $view['formdata']->record_comment;
                $view['formdata']->diaper_time = $view['formdata']->record_time;
                break;
            case "medication":
                $view['formdata']->medication_comment = $view['formdata']->record_comment;
                $view['formdata']->medication_amount = $view['formdata']->record_amount;
                $view['formdata']->medication_unit = $view['formdata']->record_unit;
	            $view['formdata']->medication_name = $view['formdata']->record_name;
                break;
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

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        foreach ($Record->getChildren($view['record']->record_id) as $child) {
            $view['formdata']->children[] = $child->child_id;
        }

        $data=array('rooms'=>$view['rooms'],'children'=>$view['children'],'types'=>$view['types'],'moods'=>$view['moods'],'record'=>$view['formdata']);

        return $res->withJson($data);
        //return $this->view->render($res, 'recordCreate.html', $view);
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

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        $record = $Record->getOne($args['record_id']);

        if ($record->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Record::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            
            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        if (!$data['children']) {
            $this->logger->info('No child selected.', [ 'user_id' => $req->getAttribute('user_id') ]);

            return $res->withJson(['error'=>'You forgot to select the children involved.']);
        }

        if( $data['general_comment'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["general_comment"]);

        }elseif($data['param']['mood'][0] != ''){
            $Record->setDetails($args['record_id'], $data, $data["mood_comment"]);
            if (count($data['param'])) {
                foreach ($data['param'] as $key => $value) {
                    if (is_array($value)) {
                        $value = array_filter($value);
                        $value = implode(', ', $value);
                    }
                    $Record->setParam($args['record_id'], $key, $value);
                }
            }

        }elseif($data['param']['meal_time'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["meal_comment"],$data['param']["meal_time"]);
            $Record->setParam($args['record_id'], 'amount', $data['param']['meal_amount']);
			$Record->setParam($args['record_id'], 'meal', $data['param']['meal']);

        }elseif($data['param']['start_nap'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["nap_comment"],$data['param']["start_nap"]);
            $Record->setParam($args['record_id'], 'end', $data['param']['end_nap']);

        }elseif($data['param']['toilet_time'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["toilet_comment"],$data['param']["toilet_time"]);

        }elseif($data['param']['diaper_time'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["diaper_comment"],$data['param']["diaper_time"]);
            $Record->setParam($args['record_id'], 'condition', $data['param']['condition']);

        }elseif($data['param']['start_medication'] != ''){
            $Record->setDetails($args['record_id'], $data, $data["medication_comment"],$data['param']['start_medication']);
            $Record->setParam($args['record_id'], 'name', $data['param']['medication_name']);
			$Record->setParam($args['record_id'], 'amount', $data['param']['medication_amount']);
			$Record->setParam($args['record_id'], 'unit', $data['param']['medication_unit']);
        }

        $Record->purgeChildren($args['record_id']);

        foreach ($data['children'] as $child_id) {
            $Record->createChild($child_id, $args['record_id']);
        }

        if ($data['media']) {
            $Record->purgeMedias($args['record_id']);

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

                $this->logger->debug('Saved media.', [ 'record_id' => $args['record_id'], 'full_url' => $resized_full->getUrl(), 'thumbnail_url' => $resized_thumbnail ]);

                $media_id = $Media->create($resized_full->getUrl(), $resized_thumbnail, $resized_full->data['mime_type']);
                $Record->createMedia($media_id, $args['record_id']);
            }
        }

        $this->logger->info('Records updated.', [ 'record_id' => $args['record_id'], 'user_id' => $req->getAttribute('user_id') ]);
        $this->flash->addMessage('success', 'Daily records have been updated.');

        return $res->withJson(['success'=>'Daily records have been updated.']);
        //return $res->withStatus(302)->withRedirect($this->router->pathFor('record'));
    });
});
