<?php

$app->group('/API/school', function() use($app) {
    /***************************************************************************
     * Get 'school'
     *
     * Return all school if teacher howerver retun children
     * 
     * param: 
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        
        if ($req->getAttribute('user')->user_type == 'T') {

            $schools= $School->getAll($req->getAttribute('user_id'));
            $schoolsUS= $School->getAllUS($req->getAttribute('user_id'));
            $data=array('schools'=>$schools,'schoolsUS'=>$schoolsUS);
            
            return $res->withJson($data);
        } else {

            $chidren= $Child->getAssociatedChildren($req->getAttribute('user_id'));
            return $res->withJson($chidren);
        }
    });

    /***************************************************************************
     * POST API 'select'
     *
     * Validate school selection
     * param: school_id
     **************************************************************************/
    $this->post('/select', function($req, $res, $args) use($app) {
        $School = new School($this);

        $data = $req->getParsedBody();

        if (!$data['school_id']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }

        if (!$School->getUser($data['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $data['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        /**
         * Store the selected school in a session value.
         */
        $_SESSION['school_id'] = $data['school_id'];

        $this->logger->info('School selected.', ['user_id' => $req->getAttribute('user_id'), 'school_id' => $data['school_id']]);

        return $res->withJson($data);
    });

    /***************************************************************************
     * GET 'create'
     *
     * Create a new school view
     **************************************************************************/
    $this->get('/create', function($req, $res, $args) use($app) {
        $Country = new Country($this);
        $School = new School($this);

        $categories = $School->getCategories();

        $countries = $Country->getCountries();

        $data=array('categories'=>$categories,'countries'=>$countries);

        return $res->withJson($data);
    })->setName('schoolCreate');

    /***************************************************************************
     * POST 'create'
     *
     * Validate school creation form
     **************************************************************************/
    $this->post('/create', function($req, $res, $args) use($app) {
        $Checklist = new Checklist($this);
        $Room = new Room($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if (!$data['name'] || !$data['category_id'] || !$data['street'] || !$data['city'] || !$data['postal_code'] || !$data['country'] || !$data['phone']) {
            $this->logger->info('User submitted incomplete form.');

            return $res->withJson(['error'=>'The form was filled out incompletely.']);
        }
        if ($data['country']=="US"){
                $school_id = $School->createUS($data);
            }else{
                $school_id = $School->create($data);
            }
        if (!$school_id) {
            $this->logger->error('School::create failed.', [ 'user_id' => $req->getAttribute('user_id') ]);

            return $res->withJson(['error'=>'Your school could not be created.']);
        }

        /**
         * Create a school-to-user association so that users are directly
         * assigned administrative role '1' for their newly created school.
         */
        $School->createUser($school_id, $req->getAttribute('user_id'), '1', 'A');

        /**
         * Give schools their first room with a default name.
         */
        $Room->create($school_id, ['name' => 'Main Room', 'description' => 'The happy room for everyone.']);

        /**
         * Give schools their custom checklists based off our 7 templates.
         */
        $categories = $Checklist->getCategories();

        for ($i = 1; $i < 8; $i++) {
            $checklist = $Checklist->getOne($i);

            $checklist_id = $Checklist->create($school_id, [ 'name' => "Developmental checklist $checklist->checklist_month_min to $checklist->checklist_month_max months", 'month_min' => $checklist->checklist_month_min, 'month_max' => $checklist->checklist_month_max ]);

            foreach($categories as $category) {
                foreach($Checklist->getAllMilestones($i, $category->category_id) as $milestone) {
                    $milestone_data = [
                        'description' => $milestone->milestone_description,
                        'category_id' => $milestone->category_id,
                        'sort' => $milestone->milestone_sort,
                    ];

                    $Checklist->createMilestone($checklist_id, $milestone_data);
                }
            }
        }

        /**
         * We also set a session value so that this new school will be
         * selected by default in future browsing.
         */
        $_SESSION['school_id'] = $school_id;

        $this->logger->info('School created.', ['school_id' => $school_id, 'user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'=>'Your school has been created.']);
    });
});
