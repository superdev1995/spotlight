<?php

$app->group('/admin', function() use($app) {
    /***************************************************************************
     * GET 'admin'
     *
     * Administration panel home
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Accident = new Accident($this);
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $Compliance = new Compliance($this);
        $Policy = new Policy($this);
        $Record = new Record($this);
        $Resource = new Resource($this);
        $School = new School($this);
        $Story = new Story($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Administration';

        $view['school_count'] = $School->getCount();
        $view['subscriber_count'] = $School->getSubscriberCount();
        $view['child_count'] = $Child->getCount();
        $view['record_count'] = $Record->getCount();
        $view['story_count'] = $Story->getCount();
        $view['checklist_count'] = $Checklist->getCount();
        $view['user_count'] = $User->getCount();
        $view['teacher_count'] = $User->getTeacherCount();
        $view['parent_count'] = $User->getParentCount();
        $view['compliance_count'] = $Compliance->getCount();
        $view['policy_count'] = $Policy->getCount();
        $view['accident_count'] = $Accident->getCount();
        $view['resource_count'] = $Resource->getCount();
        $view['resource_download_count'] = $Resource->getDownloadCount();

        return $this->view->render($res, 'admin.html', $view);
    })->setName('admin');

    /***************************************************************************
     * GET 'admin/schools'
     *
     * View all schools in the database
     **************************************************************************/
    $this->get('/schools', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Registered Pre-Schools';

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        foreach ($School->getAllSchools() as $school) {
            if ($School->getSubscriptionStatus($school->school_id)) {
                if ($school->stripe_id) {
                    $status = 'Subscribed';
                } else {
                    $status = 'Free Trial';
                }
            } else {
                $status = '';
            }

            $view['schools'][] = [
                'school_id' => $school->school_id,
                'school_name' => $school->school_name,
                'school_billing_date' => $school->school_billing_date,
                'country_id' => $school->country_id,
                'created_at' => $school->created_at,
                'status' => $status,
                'children' => $Child->getAll($school->school_id),
                'users' => $School->getUsers($school->school_id),
            ];
        }

        return $this->view->render($res, 'adminSchool.html', $view);
    })->setName('adminSchool');

    /***************************************************************************
     * GET 'admin/schools/:school_id'
     *
     * View school item details
     **************************************************************************/
    $this->get('/schools/{ school_id }', function($req, $res, $args) use($app) {
        $Country = new Country($this);
        $School = new School($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $school = $School->getOne($args['school_id']);

        if (!$school) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['title'] = $school->school_name;

        $view['school'] = $School->getOne($args['school_id']);
        $view['categories'] = $School->getCategories();
        $view['countries'] = $Country->getCountries();
        $view['users'] = $School->getUsers($args['school_id']);

        return $this->view->render($res, 'adminSchoolDetails.html', $view);
    })->setName('adminSchoolDetails');

    /***************************************************************************
     * GET 'admin/users'
     *
     * View all users (teachers and parents) in the database
     **************************************************************************/
    $this->get('/users', function($req, $res, $args) use($app) {
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Registered User Accounts';

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['users'] = $User->getAll();

        return $this->view->render($res, 'adminUser.html', $view);
    })->setName('adminUser');

    /***************************************************************************
     * GET 'admin/teachers'
     *
     * View teachers list
     **************************************************************************/
    $this->get('/teachers', function($req, $res, $args) use($app) {
        $User = new User($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Registered Teachers Accounts';

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $teachers = $User->getTeachers();

        foreach ($teachers as $teacher) {
            $status = null;
            foreach ($School->getAll($teacher->user_id) as $school) {
                if ($school->school_billing_date >= \Carbon\Carbon::now()){
                    if ($school->stripe_id) {
                        $status = 'Subscribed';
                        break;
                    } else {
                        $status = 'Free Trial';
                    }
                }
                else {
                    if (empty($status) && $school->stripe_id) {
                        $status = 'Expired';
                    }
                }
            }

            $teacher->{"school_status"} = $status;
            $view['users'][] = $teacher;

        }

        return $this->view->render($res, 'adminUser.html', $view);
    })->setName('adminTeachers');

    /***************************************************************************
     * GET 'admin/parents'
     *
     * View parents list
     **************************************************************************/
    $this->get('/parents', function($req, $res, $args) use($app) {
        $User = new User($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Registered Parents Accounts';

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $parents = $User->getParents();
        foreach ($parents as $parent) {
            $status = null;
            $currentSchool = $School->getParent($parent->user_id);
            if ($currentSchool) {
                if ($currentSchool->school_billing_date >= \Carbon\Carbon::now()){
                    if ($currentSchool->stripe_id) {
                        $status = 'Subscribed';
                    } else {
                        $status = 'Free Trial';
                    }
                }
                else {
                    if (empty($status) && $currentSchool->stripe_id) {
                        $status = 'Expired';
                    }
                }

                $parent->{"school_status"} = $status;
            }

            $view['users'][] = $parent;

        }
       

        return $this->view->render($res, 'adminUser.html', $view);
    })->setName('adminParents');

    /***************************************************************************
     * GET 'account'
     *
     * View user item details
     **************************************************************************/
    $this->get('/users/{ user_id }', function($req, $res, $args) use($app) {
        $School = new School($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $user = $User->getOne($args['user_id']);

        if (!$user) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['title'] = $user->user_first_name.' '.$user->user_last_name;

        $view['admin_user'] = $User->getOne($args['user_id']);

        if ($view['admin_user']->user_type == 'T') {
            $view['schools'] = $School->getAll($args['user_id']);
        }

        if ($view['admin_user']->user_type == 'P') {
            $view['children'] = $User->getChildren($args['user_id']);
        }

        return $this->view->render($res, 'adminUserDetails.html', $view);
    })->setName('adminUserDetails');

    /***************************************************************************
     * POST 'admin/user/delete/{id}'
     *
     * Deletes user account
     **************************************************************************/
    $this->post('/user/delete/{user_id}', function($req, $res, $args) use($app) {
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $user = $User->getOne($args['user_id']);

        if (!$user) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $User->setStatus($args['user_id'], 'D');

        $this->logger->info('Account deleted.', ['email' => $user->user_email, 'user_id' => $user->user_id]);
        $this->flash->addMessage('success', 'Account deleted');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('admin'));
    })->setName('adminUserDelete');

    /***************************************************************************
     * POST 'admin/user/confirm/{id}'
     *
     * Confirm user account
     **************************************************************************/
    $this->post('/user/confirm/{user_id}', function($req, $res, $args) use($app) {
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $user = $User->getOne($args['user_id']);

        if (!$user) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $User->setStatus($args['user_id'], 'A');

        $this->logger->info('Account confirmed.', ['email' => $user->user_email, 'user_id' => $user->user_id]);
        $this->flash->addMessage('success', 'Account confirmed');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('adminUserDetails', [user_id => $args['user_id']]));
    })->setName('adminConfirm');

    /***************************************************************************
     * POST 'admin/user/send_verification{id}'
     *
     * Send verification mail to user
     **************************************************************************/
    $this->post('/user/send_verification/{user_id}', function($req, $res, $args) use($app) {
        $User = new User($this);
        $ActivationToken = new ActivationToken($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $user = $User->getOne($args['user_id']);

        if (!$user) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $User->setStatus($args['user_id'], 'P');

        $this->mailer->send('reverification.html', [
            'to' => $user->user_email,
            'subject' => 'Please confirm your email address',
            'first_name' => $user->user_first_name,
            'token_id' => $ActivationToken->createToken($args['user_id']),
        ]);

        $this->logger->info('Confirmation mail sent.', ['email' => $user->user_email, 'user_id' => $user->user_id]);
        $this->flash->addMessage('success', 'Email sent');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('adminUserDetails', [user_id => $args['user_id']]));
    })->setName('adminReverification');

    

    /***************************************************************************
     * GET 'admin/activities'
     *
     * Framework goals - suggested activities list
     **************************************************************************/
    $this->get('/activities', function($req, $res, $args) use($app) {
        $School = new School($this);
        $Frameworks = new Frameworks($this);
        $ActivityManager = new ActivityManager($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }
        
        $allGoals = $ActivityManager->getAll();
        $goalsArray = array();
        foreach ($allGoals as $goal) {
            if (!isset($goalsArray[$goal->goal_id])) {
                $goalsArray[$goal->goal_id]['goal_id'] = $goal->goal_id;
                $goalsArray[$goal->goal_id]['goal_description'] = $goal->goal_description;
                $goalsArray[$goal->goal_id]['framework_name'] = $goal->framework_name;
                $goalsArray[$goal->goal_id]['country_id'] = $goal->country_id;
                $goalsArray[$goal->goal_id]['activities'][] = [
                    'activity_url' => $goal->activity_url,
                    'activity_id' => $goal->activity_id,
                ];
            }
            else {
                $goalsArray[$goal->goal_id]['activities'][] = [
                    'activity_url' => $goal->activity_url,
                    'activity_id' => $goal->activity_id,
                ];
            }
        }

        $view['title'] = 'Activities Links';
        $view['goals'] = $goalsArray;

        return $this->view->render($res, 'adminActivities.html', $view);
    })->setName('adminActivities');

    /***************************************************************************
     * GET 'admin/activities/edit/{framework_name}'
     *
     * Create/Edit links to activities
     **************************************************************************/
    $this->get('/activities/edit/{framework_name}[/{country_id}]', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $ActivityManager = new ActivityManager($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        if ($args['framework_name'] == 'Montessori') {
            if (!empty($args['country_id']))
                $currentFramework = $ActivityManager->getByFrameworkNameAndCountry($args['framework_name'], $args['country_id']);
            else {
                $notFoundHandler = $this->get('notFoundHandler');
                return $notFoundHandler($req, $res);
            }
        }
        else {
            $currentFramework = $ActivityManager->getByFrameworkName($args['framework_name']);
        }

        if (empty($currentFramework)){
            $notFoundHandler = $this->get('notFoundHandler');
            return $notFoundHandler($req, $res);
        }

        $goalsArray = array();
        foreach ($currentFramework as $goal) {
            if (!isset($goalsArray[$goal->goal_id])) {
                $goalsArray[$goal->goal_id]['goal_id'] = $goal->goal_id;
                $goalsArray[$goal->goal_id]['goal_description'] = $goal->goal_description;
                $goalsArray[$goal->goal_id]['activities'][] = [
                    'activity_url' => $goal->activity_url,
                    'activity_id' => $goal->activity_id,
                ];
            }
            else {
                $goalsArray[$goal->goal_id]['activities'][] = [
                    'activity_url' => $goal->activity_url,
                    'activity_id' => $goal->activity_id,
                ];
            }
        }

        $view['title'] = 'Edit activities of '.$args['framework_name'];
        $view['goals'] = $goalsArray;
        $view['framework_name'] = $args['framework_name'];

        return $this->view->render($res, 'adminEditActivities.html', $view);
    })->setName('adminEditActivities');

    /***************************************************************************
     * POST 'admin/activities/edit/{framework_name}'
     *
     * Create/Edit links to activities
     **************************************************************************/
    $this->post('/activities/edit/{framework_name}', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $ActivityManager = new ActivityManager($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $currentFramework = $ActivityManager->getByFrameworkName($args['framework_name']);
        if (empty($currentFramework) || empty($data['goals'])){
            $notFoundHandler = $this->get('notFoundHandler');
            return $notFoundHandler($req, $res);
        }

        $createGoalsArray = array();
        $updateGoalsArray = array();
        foreach ($currentFramework as $goal) {
            if (array_key_exists($goal->activity_id, $data['edited'])) {
                $updateGoalsArray[$goal->goal_id][] = [
                    'activity_id' => $goal->activity_id,
                    'activity_url' => $data['edited'][$goal->activity_id][$goal->goal_id]
                ];
            }
            else {
                if (isset($data['goals']['empty'][$goal->goal_id]))
                    $createGoalsArray[$goal->goal_id] = $data['goals']['empty'][$goal->goal_id];
            }
        }

        $ActivityManager->updateFromArray($updateGoalsArray);
        $ActivityManager->createFromArray($createGoalsArray);

        if (!empty($data['new'])){
            $ActivityManager->createFromArray($data['new']);
        }

        $this->logger->info('Activities saved.', ['framework_name' => $args['framework_name'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Activities saved.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('adminActivities'));

    })->setName('adminEditActivitiesPost');

    /***************************************************************************
     * POST 'admin/activities/delete'
     *
     * Delete Activity Link
     **************************************************************************/
    $this->post('/activities/delete', function($req, $res, $args) use($app) {
        $Frameworks = new Frameworks($this);
        $ActivityManager = new ActivityManager($this);
        $User = new User($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }


        if ($req->getAttribute('user')->user_admin != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $item = $ActivityManager->getOneByActivityId($data['activity_id']);

        if($item){
            $ActivityManager->purgeByActivityId($data['activity_id']);
        }

        $this->logger->info('Activity deleted', ['activity_id' => $data['activity_id'], 'user_id' => $req->getAttribute('user_id')]);

        return $res->withJson(['success'], 201);

    })->setName('adminDeleteActivityLink');
});