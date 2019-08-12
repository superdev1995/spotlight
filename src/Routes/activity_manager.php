<?php

$app->group('/activity_manager', function() use($app) {
    /***************************************************************************
     * GET '/goals_analysis'
     *
     * Activity manager page for the most selected goals
     **************************************************************************/
    $this->get('/goals_analysis', function($req, $res, $args) use($app) {
        $School = new School($this);
        $Frameworks = new Frameworks($this);
        $Room = new Room($this);
        $Child = new Child($this);
        $Story = new Story($this);
        $ActivityManager = new ActivityManager($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();
        
        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        if ($school_user->role != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['title'] = 'Activity Manager';
        $view['sub_title'] = 'Goals analysis';

        $currentSchool = $School->getOne($_SESSION['school_id']);

        $view['school'] = $currentSchool;
        $view['frameworks'] = $Story->getFrameworks($currentSchool->country_id);
        $view['rooms'] = $Room->getAll($currentSchool->school_id);
        $view['children'] = array();
        foreach ($view['rooms'] as $currentRoom) {
            $view['children'] = array_merge($view['children'], $Room->getChildren($currentRoom->room_id));
        }

        return $this->view->render($res, 'goalsAnalysis.html', $view);
    })->setName('goalsAnalysis');

    /***************************************************************************
     * POST '/goals_analysis'
     *
     * Activity manager page for the most selected goals
     **************************************************************************/
    $this->post('/goals_analysis', function($req, $res, $args) use($app) {
        $School = new School($this);
        $Frameworks = new Frameworks($this);
        $Room = new Room($this);
        $Child = new Child($this);
        $Story = new Story($this);
        $ActivityManager = new ActivityManager($this);
        $User = new User($this);

        $view['flash'] = $this->flash->getMessages();

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        if ($school_user->role != 1) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['title'] = 'Activity Manager';
        $view['sub_title'] = 'Goals analysis';

        $data = $req->getParsedBody();

        if (in_array($data['type'], ['most', 'least'])) {
            $view['type'] = $data['type'];
        }

        if (empty($data['start_date']))
            $data['start_date'] = null;
        else
            $view['start_date'] = $data['start_date'];

        if (empty($data['end_date']))
            $data['end_date'] = null;
        else
            $view['end_date'] = $data['end_date'];

        if(!empty($data['frameworks'])){
            $view['checked_frameworks'] = $data['frameworks'];
        }

        if(!empty($data['rooms'])){
            $view['checked_rooms'] = $data['rooms'];
        }

        if(!empty($data['children'])){
            $view['checked_children'] = $data['children'];
        }

        $goalsCountArray = array();
            $currentSchool = $School->getOne($_SESSION['school_id']);
            if (!empty($data['frameworks']) && is_array($data['frameworks']))
                $schoolFrameworks = $Story->getFrameworksByNames($currentSchool->country_id, $data['frameworks']);
            else
                $schoolFrameworks = $Story->getFrameworks($currentSchool->country_id);
            if (!empty($data['rooms']) && is_array($data['rooms']))
                $schoolRooms[$currentSchool->school_id] = $Room->getByIds($currentSchool->school_id, $data['rooms']);
            else
                $schoolRooms[$currentSchool->school_id] = $Room->getAll($currentSchool->school_id);

            foreach ($schoolRooms[$currentSchool->school_id] as $currentRoom) {
                if (!empty($data['children']) && is_array($data['children']))
                    $childrenRoom[$currentRoom->room_id] = $Room->getChildrenByIds($currentRoom->room_id, $data['children']);
                else
                    $childrenRoom[$currentRoom->room_id] = $Room->getChildren($currentRoom->room_id);
                foreach ($childrenRoom[$currentRoom->room_id] as $currentChild) {
                    if ($schoolFrameworks) {
                        foreach ($schoolFrameworks as $currentFramework) {
                            $currentGoals = $Story->getChildGoalsByFrameworkNameAndDate($currentChild->child_id, $currentFramework->framework_name, $data['start_date'], $data['end_date']);
                            if (!empty($currentGoals)) {
                                $childrenGoals[$currentChild->child_id] = $currentGoals;
                                $goalsIds = array();
                                foreach ($currentGoals as $currentGoal) {
                                    $goalsIds[] = $currentGoal->goal_id;

                                    if (!isset($goalsCountArray[$currentFramework->framework_name]['school'])) {
                                        $goalsCountArray[$currentFramework->framework_name]['school'] = [
                                            'school_id' => $currentSchool->school_id,
                                            'school_name' => $currentSchool->school_name
                                        ];
                                    }

                                    if (!isset($goalsCountArray[$currentFramework->framework_name]['school']['goals'][$currentGoal->goal_id])){
                                        $goalsCountArray[$currentFramework->framework_name]['school']['goals'][$currentGoal->goal_id] = [
                                            'goal_description' => $currentGoal->goal_description,
                                            'count' => 1
                                        ];
                                    }
                                    else {
                                        $goalsCountArray[$currentFramework->framework_name]['school']['goals'][$currentGoal->goal_id]['count']++;
                                    }

                                    if (!isset($goalsCountArray[$currentFramework->framework_name]['room'][$currentRoom->room_id])) {
                                        $goalsCountArray[$currentFramework->framework_name]['room'][$currentRoom->room_id] = [
                                            'room_id' => $currentRoom->room_id,
                                            'room_name' => $currentRoom->room_name
                                        ];
                                    }

                                    if (!isset($goalsCountArray[$currentFramework->framework_name]['room'][$currentRoom->room_id]['goals'][$currentGoal->goal_id])){
                                        $goalsCountArray[$currentFramework->framework_name]['room'][$currentRoom->room_id]['goals'][$currentGoal->goal_id] = [
                                            'goal_description' => $currentGoal->goal_description,
                                            'count' => 1
                                        ];
                                    }
                                    else {
                                        $goalsCountArray[$currentFramework->framework_name]['room'][$currentRoom->room_id]['goals'][$currentGoal->goal_id]['count']++;
                                    }

                                    if (!isset($goalsCountArray[$currentFramework->framework_name]['child'][$currentChild->child_id])) {
                                        $goalsCountArray[$currentFramework->framework_name]['child'][$currentChild->child_id] = [
                                            'child_id' => $currentChild->child_id,
                                            'child_name' => $currentChild->child_name,
                                            'child_avatar_url' => $currentChild->child_avatar_url
                                        ];
                                    }

                                    if (!isset($goalsCountArray[$currentFramework->framework_name]['child'][$currentChild->child_id]['goals'][$currentGoal->goal_id])){
                                        $goalsCountArray[$currentFramework->framework_name]['child'][$currentChild->child_id]['goals'][$currentGoal->goal_id] = [
                                            'goal_description' => $currentGoal->goal_description,
                                            'count' => 1
                                        ];
                                    }
                                    else {
                                        $goalsCountArray[$currentFramework->framework_name]['child'][$currentChild->child_id]['goals'][$currentGoal->goal_id]['count']++;
                                    }
                                }
                                if($view['type'] == 'least' && in_array($currentSchool->country_id, ['IE', 'GB']) && !empty($goalsIds)) {
                                    $goalsActivities[$currentFramework->framework_name] = $ActivityManager->getByIds($goalsIds);
                                }
                            }

                        }

                    }

                    
                }
            }
        //}
        
        $view['goals'] = $goalsCountArray;
        $view['school'] = $currentSchool;
        $view['frameworks'] = $Story->getFrameworks($currentSchool->country_id);
        $view['rooms'] = $Room->getAll($currentSchool->school_id);
        $view['children'] = array();
        foreach ($view['rooms'] as $currentRoom) {
            $view['children'] = array_merge($view['children'], $Room->getChildren($currentRoom->room_id));
        }

        if ($School->getSubscriptionStatus($_SESSION['school_id'])) {
            if($view['type'] == 'least' && in_array($currentSchool->country_id, ['IE', 'GB']) && !empty($goalsActivities)) {
                $goalsArray = array();
                foreach ($goalsActivities as $framework_name => $frameworkActivities) {
                    foreach ($frameworkActivities as $goal) {
                        if (!isset($goalsArray[$framework_name][$goal->goal_id])) {
                            $goalsArray[$framework_name][$goal->goal_id]['goal_id'] = $goal->goal_id;
                            $goalsArray[$framework_name][$goal->goal_id]['goal_description'] = $goal->goal_description;
                            $goalsArray[$framework_name][$goal->goal_id]['framework_name'] = $goal->framework_name;
                            $goalsArray[$framework_name][$goal->goal_id]['activities'][] = [
                                'activity_url' => $goal->activity_url,
                                'activity_id' => $goal->activity_id,
                            ];
                        }
                        else {
                            $goalsArray[$framework_name][$goal->goal_id]['activities'][] = [
                                'activity_url' => $goal->activity_url,
                                'activity_id' => $goal->activity_id,
                            ];
                        }
                    }
                }

                $view['goalsActivities'] = $goalsArray;
            }

            $view['isSubscribed'] = true;
        }

        $view['showResults'] = true;

        return $this->view->render($res, 'goalsAnalysis.html', $view);
    })->setName('goalsAnalysisPost');
});