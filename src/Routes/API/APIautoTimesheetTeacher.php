<?php

use \Carbon\Carbon;

  $app->group('/API', function() use($app) {

    /***************************************************************************
    * Post 'autosave'
    *
    * Write to file textfield from learning story
    *
    **************************************************************************/
    $this->post('/timesheet/teacher', function($req, $res, $args) use($app) {
        $TeacherTimesheet = new TeacherTimesheet($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        $data['timesheet_date']=date("Y-m-d");
        
        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        if ($school_user) {
            if ($data['in']=="true") {
                $TeacherTimesheet->create(
                    $data["timesheet_date"],
                    $req->getAttribute('user_id'),
                    $data['time']                );
            }else{
                $timesheet=$TeacherTimesheet->getOne(
                    $data["timesheet_date"],
                    $req->getAttribute('user_id') );
                
                $TeacherTimesheet->create(
                    $data["timesheet_date"],
                    $req->getAttribute('user_id'),
                    $timesheet->timesheet_in,
                    $data['time']
                );
            }
        }
            
    })->setName('teacher_timesheetClockInOut');

  });

?>