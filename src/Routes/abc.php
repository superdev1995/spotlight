<?php

use \Carbon\Carbon;


$app->group('/abc', function() use($app) {
    /***************************************************************************
     * GET 'abc'
     *
     * View all abc of a school
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);
		$Abc1 = new Abc($this);

        for ($i = 1; $i <= 52; $i++) {
            $view['weeks'][$i] = getStartAndEndDate($i, date('Y'));
        }
		$view['current_week'] = $_GET['week'] ? (int)$_GET['week'] : date('W');
		
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Antecedent, Behaviour and Consequence';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
		
		
        if ($req->getAttribute('user')->user_type == 'T') {
            $Abcs = $Abc->getAllDate($_SESSION['school_id'], $view['current_week']);
        }
		
        foreach ($Abcs as $abc) {
            $view['dates'][$abc->abc_date][$abc->abc_time][$abc->abc_id] = [
				'abc_id' => $abc->abc_id,
                'school_id' => $abc->school_id,
                'user_id' => $User->getOne($abc->user_id),
				'abc_assoc' => $abc->abc_assoc,
				'abc_comment' => $abc->abc_comment,
                'created_at' => $abc->created_at,
				'abc_children' => $Abc->getChildren($abc->abc_id),
				'abc_rooms' => $Abc->getRoom($abc->abc_id),
            ];
        }


        return $this->view->render($res, 'abc.html', $view);
    })->setName('abc');
    
    /***************************************************************************
     * GET 'single/{abc_id}'
     *
     * View single ABC
     **************************************************************************/
    $this->get('/single/{abc_id}', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Antecedent, Behaviour and Consequence';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
		
		
        if ($req->getAttribute('user')->user_type == 'T') {
            $Abcs = $Abc->getAllDate($_SESSION['school_id'], $view['current_week']);
        }
		
        $view['abc'] = $Abc->getOne($args['abc_id']);
		
		$view['blocks'] = $Abc->getBlocks($args['abc_id']);
		
		$view['abc_children'] = $Abc->getChildren($args['abc_id']);
		$view['abc_rooms'] = $Abc->getRoom($args['abc_id']);

        return $this->view->render($res, 'abcSingle.html', $view);
    })->setName('abcView');
	
	
    /***************************************************************************
     * GET '/create'
     *
     * Form fror create ABC
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Abc = new Abc($this);
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);
		
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.',
				['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}
		
		
		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Create ABC';

		$view['group_items'] = [
			'description' => 'Description',
			'method' => 'Method',
			'comment' => 'Comment',
		];

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];
		
        $view['date'] = date("Y-m-d");
        $view['time'] = date("H:i");
		
        return $this->view->render($res, 'abcCreate.html', $view);
    })->setName('abcCreate');
    
    /***************************************************************************
     * POST '/create'
     *
     * Create a new ABC
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Abc = new Abc($this);
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);
		
		$data = $req->getParsedBody();
		
        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }
		
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.',
				['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}
		
		$abc_id = $Abc->Create($_SESSION['school_id'], 
			$req->getAttribute('user_id'), 
			$data['abc_type'], 
			$data['method_date'], 
			$data['method_time'], 
			$data['method_antecedents'], 
			$data['method_behaviour'], 
			$data['method_consequence'], 
			$data['method_comment']);
		
		$this->flash->addMessage('success', 'ABC [ID:'. $abc_id .'] created.');
		
		$assoc_arr = array();

		if($data['abc_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['abc_type']];
		}

		$Abc->assABC($abc_id, $data['abc_type'], $assoc_arr);
		
		$Abc->addABCBlocks($abc_id, $data['day-blocks']);
		
        return $res->withStatus(302)->withRedirect($this->router->pathFor('abc'));
    })->setName('abcNew');
	
	/***************************************************************************
     * GET ':abc_id/edit'
     *
     * View form for edit ABC
     **************************************************************************/
	
    $this->get('/{abc_id}/edit', function($req, $res, $args) use($app) {
        $Abc = new Abc($this);
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);
		
		$view['flash'] = $this->flash->getMessages();
		
        $view['title'] = 'Edit ABC';

        $view['abc'] = $Abc->getOne($args['abc_id']);
		
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.',
				['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}
		
		$view['formdata'] = $view['abc'];
		
		$view['children'] = $Child->getAll($_SESSION['school_id']);
		$view['rooms'] = $Room->getAll($_SESSION['school_id']);

		$view['group_items'] = [
			'description' => 'Description',
			'method' => 'Method',
			'comment' => 'Comment',
		];

		$view['types'] = [
			'school' => 'School',
			'room' => 'Room',
			'child' => 'Child',
		];
		
        $view['date'] = date("Y-m-d");
        $view['time'] = date("H:i");
		
		foreach ($Abc->getChildren($view['abc']->abc_id) as $child) {
            $view['formdata']->child[] = $child->child_id;
        }
		
		foreach ($Abc->getRoom($view['abc']->abc_id) as $room) {
            $view['formdata']->room[] = $room->room_id;
        }
		
		$view['blocks'] = $Abc->getBlocks($args['abc_id']);
		
        return $this->view->render($res, 'abcCreate.html', $view);
    })->setName('abcEdit');


	/***************************************************************************
     * POST ':abc_id/edit'
     *
     * Save edit ABC
     **************************************************************************/
    $this->post('/{abc_id}/edit', function($req, $res, $args) use($app) {
        $Abc = new Abc($this);
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);
		
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

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('abc'));
        }

        $abc = $Abc->getOne($args['abc_id']);

        if ($abc->school_id != $_SESSION['school_id']) {
            $this->logger->notice('Abc::getEdit failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('abc'));
        }
		
		$abc_id = $Abc->Update($args['abc_id'],
			$data['abc_type'],  
			$data['method_comment']);
			
		$abc_id = $Abc->purgeASS($args['abc_id']);
		$Abc->purgeBlocks($args['abc_id']);
		
		$this->flash->addMessage('success', 'ABC [ID:'. $args['abc_id'] .'] updated.');
		
		$assoc_arr = array();

		if($data['abc_type'] == 'school') {
			$assoc_arr[] = $_SESSION['school_id'];
		} else {
			$assoc_arr = $data[$data['abc_type']];
		}

		$Abc->assABC($args['abc_id'], $data['abc_type'], $assoc_arr);
		$Abc->addABCBlocks($args['abc_id'], $data['day-blocks']);
		
        return $res->withStatus(302)->withRedirect($this->router->pathFor('abcView', [ 'abc_id' => $args['abc_id'] ] ) );
    })->setName('abcDone');
    
    /***************************************************************************
     * POST ''
     *
     * Delete ABC
     **************************************************************************/
    $this->post('', function($req, $res, $args) use($app) {
        $Abc = new Abc($this);
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
		$User = new User($this);
		$Abc = new Abc($this);
		
		$data = $req->getParsedBody();
		
        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }
		
		if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
			$this->logger->info('School::getUser failed.',
				['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
			$this->flash->addMessage('danger', 'You don’t have sufficient rights.');

			return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
		}
		
		$abc_id = $Abc->purge($data['abc_id']);
		$Abc->purgeASS($data['abc_id']);
		$Abc->purgeBlocks($data['abc_id']);
		
		$this->flash->addMessage('success', 'ABC [ID:'. $data['abc_id'] .'] deleted.');
		
        return $res->withStatus(302)->withRedirect($this->router->pathFor('abc'));
    })->setName('deleteABC');
});