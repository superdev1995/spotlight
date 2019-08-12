<?php

$app->group('', function() use($app) {

    /***************************************************************************
     * GET 'frameworks'
     *
     * View to manage frameworks
     **************************************************************************/

    $this->get('/frameworks', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Framework Overview';

        $school = $School->getOne($_SESSION['school_id']);
        $view['country_id'] =$school->country_id;
        //$view['checklists_global'] = $Checklist->getAllGlobal($school->country_id);
        $view['frameworks'] = $Frameworks->getAllSchool($_SESSION['school_id']);


        return $this->view->render($res, 'schoolFramework.html', $view);
    })->setName('schoolFramework');

    /***************************************************************************
     * GET 'frameworks/:framework_id'
     *
     * View framework item
     **************************************************************************/

    $this->get('/frameworks/{framework_id}', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Framework Category Editor';

        $view['framework'] = $Frameworks->getOne($args['framework_id']);
        //$view['all_categories'] = $Frameworks->getAllCategories();



       // foreach ($Frameworks->getAllCategories() as $category) {
       //     $view['categories_goals'][$category->category_name] = $Frameworks->getAllGoals($args['framework_id'], $category->category_id);
       // }
        $categories = $Frameworks->getCategories($args['framework_id']);
        foreach ( $categories as $category) {
            $view['categories'][$category->category_id] = [
            'category_name' => $category->category_name,     
            'category_id' => $category->category_id,  
            'category_group' => $category->category_group,
            'category_description' => $category->category_description,
            'goals' => $Frameworks->getAllGoals($args['framework_id'],$category->category_id)
        ];

        }

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        return $this->view->render($res, 'schoolFrameworkDetails.html', $view);
    })->setName('schoolFrameworkDetails');

    /***************************************************************************
     * POST 'frameworks/create'
     *
     * Validate framework creation form
     **************************************************************************/

    $this->post('/frameworks/create', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if ($data['month_max'] < $data['month_min']) {
            $this->logger->notice('Age range invalid.', [ 'user_id' => $req->getAttribute('user_id'), 'month_min' => $data['month_min'], 'month_max' => $data['month_max'] ]);
            $this->flash->addMessage('danger', 'The duration range is invalid. Minimum years must be smaller than maximum years.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFramework'));
        }

        $framework_id = $Frameworks->create($_SESSION['school_id'], $data);

        $this->logger->info('School framework created.', [ 'framework_id' => $framework_id, 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'A new framework has been created for your school.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFramework'));
    })->setName('schoolFrameworkCreate');

    /***************************************************************************
     * POST 'frameworks/delete'
     *
     * Validate frameworks deletion
     **************************************************************************/
    $this->post('/frameworks/delete', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $framework = $Frameworks->getOne($data['framework_id']);

        if ($_SESSION['school_id'] != $framework->school_id) {
            $this->logger->warning('Framework school ID is invalid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFramework'));
        }

        $Frameworks->setStatus($data['framework_id'], 'D');

        $this->logger->info('School framework deleted.', [ 'framework_id' => $data['framework_id'], 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'Your Framework has been deleted.' );

        return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFramework'));
    })->setName('schoolFrameworkDelete');

     /***************************************************************************
     * POST 'frameworks/:framework_id/create-category'
     *
     * Validate category creation form
     **************************************************************************/

    $this->post('/frameworks/{framework_id}/create-category', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $framework = $Frameworks->getOne($args['framework_id']);

        if ($_SESSION['school_id'] != $framework->school_id) {
            $this->logger->warning('Checklist school ID is invalid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFramework'));
        }

        $category_id = $Frameworks->createCategory($args['framework_id'], $data);
        $Frameworks->createGoal($category_id, $data);

        $this->logger->info('Framework category created.', [ 'category_id' => $category_id ]);
        $this->flash->addMessage('success', 'A category has been added to the checklist.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $args['framework_id'] ]));
    })->setName('schoolCategoryCreate');

    /***************************************************************************
     * POST 'frameworks/:framework_id/edit'
     *
     * Validate framework editing form
     **************************************************************************/
    $this->post('/frameworks/{framework_id}/edit', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $framework = $Frameworks->getOne($args['framework_id']);

        if ($_SESSION['school_id'] != $framework->school_id) {
            $this->logger->warning('Framework school ID is invalid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFramework'));
        }

        if ($data['month_min'] > $data['month_max']) {
            $this->logger->info('Framework update rejected.', [ 'school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'The recommended minimum month cannot be larger than the maximum month.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $args['framework_id'] ]));
        }

        $Frameworks->setFramework($args['framework_id'], $data);

        $this->logger->info('School framework updated.', [ 'framework_id' => $args['framework_id'], 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'Your framework has been updated.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $args['framework_id'] ]));
    })->setName('schoolFrameworkEdit');


    /***************************************************************************
     * POST 'frameworks/goals/delete'
     *
     * Validate goal deletion
     **************************************************************************/

    $this->post('/goals/delete', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $goal = $Frameworks->getGoal($data['goal_id']);

        $category=$Frameworks->getCategory($goal->category_id);
        $framework = $Frameworks->getOne($category->framework_id);
        

        if ($_SESSION['school_id'] != $framework->school_id) {
            $this->logger->warning('Framework school ID is invalid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $framework->framework_id ]));
        }

        $Frameworks->setGoalStatus($data['goal_id'], 'D');

        $this->logger->info('Framework goal deleted.', [ 'framework_id' => $category->framework_id, 'goal_id' => $data['goal_id'] ]);
        $this->flash->addMessage('success', 'The observation goal has been deleted.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $framework->framework_id ]));
    })->setName('schoolGoalDelete');

    /***************************************************************************
     * POST 'frameworks/group/edit'
     *
     * Validate group editing form
     **************************************************************************/
    $this->post('/group/edit', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $ret = "success";
        $reterr = "failed";
        $data = $req->getParsedBody();
     
        
        $Frameworks->setGroup($data);


        $this->logger->info('Goal updated.', [ 'framework_id' => $data['framework_id'], 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'The observation group has been updated.');
       
           
        return $res->withJSON(
            $ret,
            200
            
        );
        
        })->setName('schoolGroupEdit');

      /***************************************************************************
     * POST 'frameworks/group/edit'
     *
     * Validate group editing form
     **************************************************************************/
    $this->post('/group/delete', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $ret = "success";
        $reterr = "failed";
        $data = $req->getParsedBody();
     
        
        $Frameworks->deleteGroup($data);


        $this->logger->info('Goal updated.', [ 'framework_id' => $data['framework_id'], 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'The observation group has been updated.');
       
           
        return $res->withJSON(
            $ret,
            200
            
        );
        
        })->setName('schoolGroupDelete');

    /***************************************************************************
     * POST 'frameworks/category/edit'
     *
     * Validate category editing form
     **************************************************************************/
    $this->post('/category/edit', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);
        $ret = "success";
        $reterr = "failed";
        $data = $req->getParsedBody();
      

        $category = $Frameworks->getCategory($data['category_id']);
        $framework = $Frameworks->getOne($category->framework_id);

        if ($_SESSION['school_id'] != $framework->school_id) {
            $this->logger->warning('Framework school ID is invalid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');
            return $res->withJSON(
                $reterr,
                200
                
            );
        }

        $Frameworks->setCategory($data['category_id'], $data);
        $Frameworks->purgeGoals($data['category_id']);
        $counter=0;
        foreach($data['goals'] as $item) 
        {
            $counter++;
            $Frameworks->createGoalOne($data['category_id'], $item,$counter);
        }
       

        $this->logger->info('Category updated.', [ 'framework_id' => $data['framework_id'], 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'The observation category has been updated.');
             
        return $res->withJSON(
            $ret,
            200
            
        );
      
       // return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $framework->framework_id ]));
    })->setName('schoolCategoryEdit');



    $this->post('/category/delete', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $category=$Frameworks->getCategory($data['category_id']);
        $framework = $Frameworks->getOne($category->framework_id);


        if ($_SESSION['school_id'] != $framework->school_id) {
            $this->logger->warning('Framework school ID is invalid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $framework->framework_id ]));
        }

        if(!$data['category_id']) {
            $this->logger->warning('Category id not valid.', [ 'school_id' => $_SESSION['school_id'], 'framework_school_id' => $framework->school_id ]);
            $this->flash->addMessage('danger', 'Not valid.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $framework->framework_id ]));
        }

        $category_id = $Frameworks->purgeCategory($data['category_id']);
        $Frameworks->purgeGoals($category_id);

        $this->logger->info('Category deleted.', [ 'framework_id' => $args['framework_id'], 'school_id' => $_SESSION['school_id'] ]);
        $this->flash->addMessage('success', 'The category has been deleted.');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolFrameworkDetails', [ 'framework_id' => $framework->framework_id ]));
    })->setName('schoolCategoryDelete');

});