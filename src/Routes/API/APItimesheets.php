<?php

use \Carbon\Carbon;

$app->group('/API/timesheets', function() use($app) {
    /***************************************************************************
     * GET 'timesheets/:date'
     *
     * Redirect to the current date
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Timesheet = new Timesheet($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Attendance';

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        $view['current_week'] = isset($_GET['week']) ? str_pad($_GET['week'], 2, "0", STR_PAD_LEFT) : date('W');
        $view['current_date'] = date('Y-m-d');
        
        for ($i = 1; $i <= 52; $i++) {
            $view['weeks'][$i] = getStartAndEndDate($i, date('Y'));
        }

        for($i = 1; $i <= 7; $i++) {
            $view['dates'][] = date('Y-m-d', strtotime(date('Y')."W".$view['current_week'].$i));
        }

        $total_duration = 0;

        foreach($view['children'] as $child) {
            $child_total_duration = 0;

            foreach($view['dates'] as $date) {
                $timesheet = $Timesheet->getOne($date, $child->child_id);
                $view['timesheets'][$child->child_id][$date] = $timesheet;
    
                if (!$timesheet) {
                    $view['totals'][$child->child_id][$date] = '/';
                    continue;
                }
    
                if ($timesheet->missing) {
                    switch ($timesheet->missing) {
                        case 'Absent':
                            $view['totals'][$child->child_id][$date] = 'Abs.';
                            break;
                        case 'Sick':
                            $view['totals'][$child->child_id][$date] = 'Sick';
                            break;
                        case 'Holiday':
                            $view['totals'][$child->child_id][$date] = 'Hol.';
                            break;
                    }
                    
                    continue;
                }
                
                if ($timesheet->timesheet_in && !$timesheet->timesheet_out) {
                    $view['totals'][$child->child_id][$date] = 'IN';
                    continue;
                }
                
                $time_in = Carbon::parse($timesheet->timesheet_in);
                $time_out = Carbon::parse($timesheet->timesheet_out);
                $day_duration = $time_out->diffInSeconds($time_in);
                $formatted_duration = gmdate('H:i', $day_duration);
                $view['totals'][$child->child_id][$date] = $formatted_duration;
                $child_total_duration += $day_duration;
            }

            $total_duration += $child_total_duration;

            $child_hours = gmdate('H', $child_total_duration) + (gmdate('d', $child_total_duration) - 1) * 24;
            $child_minutes = gmdate('i', $child_total_duration);

            $view['child_totals'][$child->child_id] = "$child_hours:$child_minutes";
        }

        $total_hours = gmdate('H', $total_duration) + (gmdate('d', $total_duration) - 1) * 24;
        $total_minutes = gmdate('i', $total_duration);

        $view['week_total'] = "$total_hours:$total_minutes";

        $data=array('week_total'=>$view['week_total'],'child_totals'=>$view['child_totals'],'rooms'=>$view['rooms'],
        'children'=>$view['children'],'weeks'=>$view['weeks'],'dates'=>$view['dates'],'totals'=>$view['totals'],'current_week'=>$view['current_week']);

        return $res->withJSON($data);
        //return $this->view->render($res, 'timesheet.html', $view);
    })->setName('timesheet');

    /***************************************************************************
     * GET 'timesheets/:date/create'
     *
     * View timesheet weekly overview
     **************************************************************************/
    $this->get('/{ date }/create', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Timesheet = new Timesheet($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Create Attendance Report for '.$args['date'];

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        $view['current_date'] = $args['date'];

        foreach($view['children'] as $child) {
            $view['timesheets'][$child->child_id] = $Timesheet->getOne($args['date'], $child->child_id);
        }

        /**
         * Iterate through all dates of this year.
         */
        $begin = new DateTime(date('Y').'-01-01');
        $end = new DateTime(date('Y').'-12-31');

        $daterange = new DatePeriod($begin, new DateInterval('P1D'), $end);

        foreach($daterange as $date){
            $view['dates'][] = $date->format("Y-m-d");
        }

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        $data=array('rooms'=>$view['rooms'],'children'=>$view['children'],'timesheets'=>$view['timesheets'],'dates'=>$view['dates']);

        return $res->withJson($data);
        //return $this->view->render($res, 'timesheetCreate.html', $view);
    })->setName('timesheetCreate');

    /***************************************************************************
     * POST 'timesheets/:date/create'
     *
     * Validate timesheet form and redirect
     **************************************************************************/
    $this->post('/{ date }/create', function($req, $res, $args) use($app) {
        $Timesheet = new Timesheet($this);

        $data = $req->getParsedBody();
        
        $this->flash->addMessage('formdata', $data);
        foreach($data['children'] as $child) {

            /*if (strtotime(date($data['ins'][$child])) > strtotime(date($data['outs'][$child]))) {
                $this->logger->notice('Invalid times submitted.');
                $this->flash->addMessage('danger', 'The time in must be earlier than time out.');

                return $res->withStatus(302)->withRedirect($this->router->pathFor('timesheetCreate', [ date => $data['timesheet_date'] ]));
            }*/
            if ($data['ins'][$child] || $data['outs'][$child]) {
                $Timesheet->create($child, $data['ins'][$child], $data['outs'][$child], $data);
            }
            $Timesheet->absent($data['timesheet_date'], $child, $data['missing'][$child], $data['comments'][$child], $data['upload'][$child]);
        }

        $this->logger->info('Timesheet saved.', [ 'user_id' => $req->getAttribute('user_id') ]);
        
        return $res->withJson(['success'=>'The attendance timesheet was saved.']);
        //return $res->withStatus(302)->withRedirect($this->router->pathFor('timesheet'));
    })->setName('timesheetCreate');

    /***************************************************************************
     * POST 'timesheets/:date/reset'
     *
     * Reset timesheet form and redirect
     **************************************************************************/
    $this->post('/{ date }/reset', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);
        $Timesheet = new Timesheet($this);

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

            return $res->withJson(['error'=>'You don’t have sufficient rights.']);
        }

        foreach($Child->getAll($_SESSION['school_id']) as $child) {
            $Timesheet->purge($args['date'], $child->child_id);
        }

        $this->logger->info('Timesheet reset.', [ 'user_id' => $req->getAttribute('user_id') ]);

        return $res->withJson(['success'=>'The attendance timesheet was reset.']);
        //return $res->withStatus(302)->withRedirect($this->router->pathFor('timesheetCreate', [ date => $args['date'] ]));
    })->setName('timesheetReset');
});
