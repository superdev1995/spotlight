<?php

use \Carbon\Carbon;

  $app->group('/API', function() use($app) {

    /***************************************************************************
    * Post 'autosave'
    *
    * Write to file textfield from learning story
    *
    **************************************************************************/
    $this->post('/invoice/totaltime', function($req, $res, $args) use($app) {
        $Timesheet = new Timesheet($this);

        $data = $req->getParsedBody();
        $timesheets = $Timesheet->getAllBetweenDate($data);
        $child_total_duration = 0;

        foreach($timesheets as $timesheet) {
            if (!$timesheet) {
                continue;
            }
    
            if ($timesheet->missing) { 
                continue;
            }
                
            if ($timesheet->timesheet_in && !$timesheet->timesheet_out) {
                continue;
            }

            $time_in = Carbon::parse($timesheet->timesheet_in);
            $time_out = Carbon::parse($timesheet->timesheet_out);
            $day_duration = $time_out->diffInSeconds($time_in);
            $formatted_duration = gmdate('H:i', $day_duration);

            $child_total_duration += $day_duration;

        }
        
        $total_hours = gmdate('H', $child_total_duration) + (gmdate('d', $child_total_duration) - 1) * 24;
        $total_minutes = (((gmdate('i', $child_total_duration))*0.5)/30);

        $total = $total_hours +$total_minutes;
       return $res->withJson($total);
     })->setName('totaltime');

  });

?>
