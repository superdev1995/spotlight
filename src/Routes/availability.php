<?php

use Dompdf\Dompdf;
use Dompdf\Options;

$app->group('/availability', function() use($app) {
       /***************************************************************************
     * GET ''
     *
     * View the teacher abstence for each month
     **************************************************************************/
	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
        $Availability = new Availability($this);
        $Calendar = new Calendar($this);
        $School = new School($this);
        $view['ReasonOfAbsence']=array(
            "Vacation",
            "Personal",
            "Sick",
            "Custom"
        );
    
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Availability';
  
        $school_object = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $arrayOfSchool = json_decode(json_encode($school_object), True); //convert object to an array
        $view['teacher_id']=$arrayOfSchool['user_id'];
        $view['school']=$arrayOfSchool;
        $view['teacher'] = $Availability->userName($arrayOfSchool['user_id']);
        $view['TeacherAdminName'] = $view['teacher']['user_first_name']." ".$view['teacher']['user_last_name'];
        $view['schoolName'] = $Availability->schoolName($arrayOfSchool['school_id']);
        $view['school_id']=$arrayOfSchool['school_id'];
       
        return $this->view->render($res, 'availability.html', $view);
    })->setName( 'availability' );

    /***************************************************************************
     * POST ''
     *
     * Request form FOR NEW EVENT
     **************************************************************************/

    $this->POST( '', function ( $req, $res, $args ) use ( $app ) {
        $Availability = new Availability($this);
        $School = new School($this);
       
        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['ReasonOfAbsence']=array(
            "Vacation",
            "Personal",
            "Sick",
            "Custom"
        );
        $data = $req->getParsedBody();
        //return $res->withJSON($data);
    
    #if it comes fromthe first modal, to create
    if(isset($data['modalEvents'])){
        if (!$data['name'] || !$data['startEvent'] || !$data['hourStartEvent'] || !$data['endEvent'] ||
                !$data['hourEndEvent'] || !$data['textColor'] || !$data['backgroundColor']){
            $this->flash->addMessage('danger', 'Select all the parameteres, please');
            return $res->withStatus(302)->withRedirect($this->router->pathFor('availability'));
        }
        else{
            if (isset($_REQUEST['eventReason']) && $_REQUEST['eventReason'] != 'Custom'){
                $view['reason'] = $_REQUEST['eventReason'];
            }
            elseif ((isset($_REQUEST['customReason']))){
                $view['reason'] = $_REQUEST['customReason'];          
            }
            else{
                $this->flash->addMessage('danger', 'Choose a reason.');
                return $res->withStatus(302)->withRedirect($this->router->pathFor('availability'));
            }
        }
        $name = $data['name'];
        $startEvent = $data['startEvent'];
        $hourStartEvent = $data['hourStartEvent'];
        $endEvent = $data['endEvent'];
        $hourEndEvent = $data['hourEndEvent'];
        $textColor = $data['textColor'];
        $backgroundColor = $data['backgroundColor']; 
        $title = $view['reason'] . ": " . $name;
        
        //teacher things
        $school_object = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $arrayOfSchool = json_decode(json_encode($school_object), True); //convert object to an array

        $view['school']=$arrayOfSchool;
        $view['school_id'] = $arrayOfSchool['school_id'];
        $view['teacher_id'] = $arrayOfSchool['user_id'];

        $view['teacher'] = $Availability->userName($arrayOfSchool['user_id']);
        $view['TeacherAdminName'] = $view['teacher']['user_first_name']." ".$view['teacher']['user_last_name'];
        $view['schoolName'] = $Availability->schoolName($arrayOfSchool['school_id']);
     
        $view['role']=$arrayOfSchool['role'];

        if ($view['role'] != "1"){
            $startEvent="$startEvent[6]$startEvent[7]$startEvent[8]$startEvent[9]-$startEvent[3]$startEvent[4]-$startEvent[0]$startEvent[1]";
            $endEvent="$endEvent[6]$endEvent[7]$endEvent[8]$endEvent[9]-$endEvent[3]$endEvent[4]-$endEvent[0]$endEvent[1]";
            $Availability->insertReason($view['teacher_id'], $view['school_id'], $title, $startEvent, $hourStartEvent, $endEvent, $hourEndEvent, $textColor, $backgroundColor);
        }
        else{
            $view['status']="Approved";
           
            $startEvent="$startEvent[6]$startEvent[7]$startEvent[8]$startEvent[9]-$startEvent[3]$startEvent[4]-$startEvent[0]$startEvent[1]";
            $endEvent="$endEvent[6]$endEvent[7]$endEvent[8]$endEvent[9]-$endEvent[3]$endEvent[4]-$endEvent[0]$endEvent[1]";
          
            $Availability->insertReasonAdmin($view['teacher_id'],$view['school_id'], $title, $startEvent, $hourStartEvent, $endEvent, $hourEndEvent, $view['status'], $textColor, $backgroundColor);
        }
    }
    elseif(isset($_REQUEST['modalEventsUpdate']) and isset($_REQUEST['btnUpdate'])){
        if ((!$data['idEvent'] || !$data['nameUpdate'] || !$data['startEventUpdate'] || !$data['hourStartEventUpdate'] || !$data['endEventUpdate'] ||
                !$data['hourEndEventUpdate'] || !$data['textColorUpdate'] || !$data['backgroundColorUpdate'])){
            $this->flash->addMessage('danger', 'Select all the parameteres for update.' . $data['idEvent']);
            return $res->withStatus(302)->withRedirect($this->router->pathFor('availability'));
        }
        else{
            if (isset($_REQUEST['eventReasonUpdate'])  && $_REQUEST['eventReasonUpdate'] != 'Custom'){
                $view['reason'] = $_REQUEST['eventReasonUpdate'];
            }
            elseif ((isset($_REQUEST['customReasonUpdate']))){
                $view['reason'] = $_REQUEST['customReasonUpdate'];          
            }
            else{
                $this->flash->addMessage('danger', 'Choose a reason.');
                return $res->withStatus(302)->withRedirect($this->router->pathFor('availability'));
            }
        }
      
        $school_object = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $arrayOfSchool = json_decode(json_encode($school_object), True); //convert object to an array

        $view['school_id'] = $arrayOfSchool['school_id'];
        $view['teacher_id'] = $arrayOfSchool['user_id'];
        $idEvent=$_REQUEST['idEvent'];
        $name=$_REQUEST['nameUpdate'];
        $startEvent=$_REQUEST['startEventUpdate'];
        $hourStartEvent=$_REQUEST['hourStartEventUpdate'];
        $endEvent=$_REQUEST['endEventUpdate'];
        $hourEndEvent=$_REQUEST['hourEndEventUpdate'];
        $textColor=$_REQUEST['textColorUpdate'];
        $backgroundColor=$_REQUEST['backgroundColorUpdate']; 
        $title = $view['reason'] . ": " . $name;

        //teacher things
       
        $view['school']=$arrayOfSchool;
        $view['teacher'] = $Availability->userName($arrayOfSchool['user_id']);
        $view['TeacherAdminName'] = $view['teacher']['user_first_name']." ".$view['teacher']['user_last_name'];
        $view['schoolName'] = $Availability->schoolName($arrayOfSchool['school_id']);
     
        $view['role']=$arrayOfSchool['role'];

        if ($view['role'] != "1"){
            $startEvent="$startEvent[6]$startEvent[7]$startEvent[8]$startEvent[9]-$startEvent[3]$startEvent[4]-$startEvent[0]$startEvent[1]";
            $endEvent="$endEvent[6]$endEvent[7]$endEvent[8]$endEvent[9]-$endEvent[3]$endEvent[4]-$endEvent[0]$endEvent[1]";
            $Availability->updateReason($view['teacher_id'],$view['school_id'], $title, $startEvent,$hourStartEvent,$endEvent,$hourEndEvent, $textColor, $backgroundColor,$idEvent);
        }
        else{
            $view['status']="Approved";
        
            $startEvent="$startEvent[6]$startEvent[7]$startEvent[8]$startEvent[9]-$startEvent[3]$startEvent[4]-$startEvent[0]$startEvent[1]";
            $endEvent="$endEvent[6]$endEvent[7]$endEvent[8]$endEvent[9]-$endEvent[3]$endEvent[4]-$endEvent[0]$endEvent[1]";
            $Availability->updateReasonAdmin($view['teacher_id'],$view['school_id'], $title, $startEvent,$hourStartEvent,$endEvent,$hourEndEvent, $view['status'], $textColor, $backgroundColor,$idEvent);
        }   
    }
    elseif(isset($_REQUEST['modalEventsUpdate']) and isset($_REQUEST['btnDel'])){
        $idEvent=$_REQUEST['idEvent'];
      
        // School things
        $school_object = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $arrayOfSchool = json_decode(json_encode($school_object), True); //convert object to an array
        $view['school_id'] = $arrayOfSchool['school_id'];
        $view['teacher_id'] = $arrayOfSchool['user_id'];
        $view['school']=$arrayOfSchool;
        $view['teacher'] = $Availability->userName($arrayOfSchool['user_id']);
        $view['TeacherAdminName'] = $view['teacher']['user_first_name']." ".$view['teacher']['user_last_name'];
        $view['schoolName'] = $Availability->schoolName($arrayOfSchool['school_id']);
     
        $Availability->deleteReason($idEvent);
    }

    return $this->view->render( $res, 'availability.html', $view );
});

   

/***********************************************************
*  GET '/check'
*
* SHOW DATA IN TABLE
*************************************************************/

$this->get( '/check', function ( $req, $res, $args ) use ( $app ) {
    $Availability = new Availability($this);
    $School = new School($this);   
    
    $view['title'] = 'Check Availability';


    $actualDate = $_REQUEST['actualDate'];

    $actualDate = explode("/", $actualDate);
    $view['year'] = "$actualDate[0]";
    $view['month'] = "$actualDate[1]";
    $start = $view['year']."-".$view['month'];
    
    
    /******************TEACHER INFO****************** */
    if ($req->getAttribute('user')->user_type == 'T') {
        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
       }
       $arrayOfSchool = json_decode(json_encode($view['school_user']), True); //convert object into array
       $view['schoolName'] = $Availability->schoolName($arrayOfSchool['school_id']);
       $view['role']=$arrayOfSchool['role'];
      
       $view['teacher'] = $Availability->userName($arrayOfSchool['user_id']);
       $view['TeacherAdminName'] = $view['teacher']['user_first_name']." ".$view['teacher']['user_last_name'];


    
     /******************REASONS****************** */  
       $view['teacherReasons']= $Availability->teacherSchoolReasons($arrayOfSchool['school_id'], $start); //all the teacher absences
       $view['teacherSchools'] = $Availability->teacherSchools($arrayOfSchool['user_id']); //le paso el id del admin para que solo pueda ver los suyos con toda su info
       
       for ($i=0;$i<count($view['teacherReasons']);$i++){
        $view['teachersName'][$i]= $Availability->selectTeacherName( $view['teacherReasons'][$i]['teacher_id']);
        $view['schoolName'][$i]= $Availability->selectSchoolName( $view['teacherReasons'][$i]['school_id']);
       
        $view['teacherReasons'][$i]['teacherName'] = $view['teachersName'][$i]['teacherName'];
        $view['teacherReasons'][$i]['schoolName'] = $view['schoolName'][$i]['school_name'];
        $view['teacherReasons'][$i]['start_hour']= $view['teacherReasons'][$i]['start_hour'][0].$view['teacherReasons'][$i]['start_hour'][1].$view['teacherReasons'][$i]['start_hour'][2].$view['teacherReasons'][$i]['start_hour'][3].$view['teacherReasons'][$i]['start_hour'][4];
        $view['teacherReasons'][$i]['end_hour']= $view['teacherReasons'][$i]['end_hour'][0].$view['teacherReasons'][$i]['end_hour'][1].$view['teacherReasons'][$i]['end_hour'][2].$view['teacherReasons'][$i]['end_hour'][3].$view['teacherReasons'][$i]['end_hour'][4];
    } 
       return $this->view->render( $res, 'availabilityCheck.html', $view );
    })->setName( 'availabilityCheck' );


/***********************************************
 * POST '/decide' 
 * 
 * APPROVE OR DENY ABSENCE
 * ********************************************/
$this->POST( '/decide', function ( $req, $res, $args ) use ( $app ) { 
    
    $Availability = new Availability($this);
    $view['title'] = 'Approve Availability';

    $reason_id=$_REQUEST['reason_id'];

    if (isset($_REQUEST['approve'])){
         $Availability->approveAvailability($reason_id);
    }elseif (isset($_REQUEST['deny'])){
         $Availability->denyAvailability($reason_id);
    }else{
        return $res->withStatus(302)->withRedirect($this->router->pathFor('availability'));
    }
    $month = $view['month'];

    return $res->withStatus(302)->withRedirect($this->router->pathFor('availabilityCheck',['month' => $month, 'year'=> $year]));

})->setName( 'availabilityApproveorDeny' );

/***********************************************
 * GET '/yearAbsences'
 * 
 * PDF Year absences
 * ********************************************/
$this->get( '/yearAbsences', function ( $req, $res, $args ) use ( $app ) { 
    $Availability = new Availability($this);
    $School = new School($this);
    $view['title'] = 'Year absences';
    
    $view['year'] = $_REQUEST['actualDate'];  
    $view['school_Name']=$_REQUEST['school'];
    
    $school_object = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
    $arrayOfSchool = json_decode(json_encode($school_object), True); //convert object to an array
   
    $view['teacher_id'] = $arrayOfSchool['user_id'];
    $view['school_id'] = $arrayOfSchool['school_id'];

    $view['teacher'] = $Availability->userName($arrayOfSchool['user_id']);
    $view['teacherName'] = $view['teacher']['user_first_name']." ".$view['teacher']['user_last_name'];
    

    $view['teacherReasons']= $Availability->selectTeacherAllReasons($arrayOfSchool['school_id'], $view['year']); //all the teacher absences

    for ($i=0;$i<count($view['teacherReasons']);$i++){
        $view['teachersName'][$i]= $Availability->selectTeacherName( $view['teacherReasons'][$i]['teacher_id']);
        $view['schoolName'][$i]= $Availability->selectSchoolName( $view['teacherReasons'][$i]['school_id']);
       
        $view['teacherReasons'][$i]['teacherName'] = $view['teachersName'][$i]['teacherName'];
        $view['teacherReasons'][$i]['schoolName'] = $view['schoolName'][$i]['school_name'];
    }

   

    /*****************************
     * 
     * FORMAT TO PDF
     ******************************/
    $dompdf = new Dompdf();
    $dompdf->set_option('defaultFont', 'Courier');
    $dompdf->set_option('isHtml5ParserEnabled', true);
    $dompdf->set_option('isRemoteEnabled',true);

 $content = $this->view->fetch('availabilityYear.html', $view);

   $dompdf->loadHtml($content);
   $dompdf->setPaper('A4', 'portrait');
   $dompdf->render();

   $data = $req->getParsedBody();



   $file_name = 'yearAbsences_'.$view['teacher_id'].'.pdf';
   $out = $dompdf->output($file_name);
   file_put_contents('yearAbsences/'.$file_name, $out);
   
   return $res->withStatus(200)
   ->withHeader('Content-Type', 'application/pdf')
   ->write($out); 

})->setName( 'availabilityYear' );


/***********************************************
 * GET '/calendar'
 * 
 * PDF Year absences
 * ********************************************/
$this->GET( '/calendar', function ( $req, $res, $args ) use ( $app ) {
    $Availability = new Availability($this);
    $School = new School($this);

    $school_object = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
    $arrayOfSchool = json_decode(json_encode($school_object), True); //convert object to an array
    $view['teacher_id']=$arrayOfSchool['user_id'];
    $view['school']=$arrayOfSchool;
    $view['school_id']=$arrayOfSchool['school_id'];
    $events = $Availability->selectEvents($view['teacher_id'],$view['school_id']);
    return $res->withJSON($events);
})->setName( 'availabilityCalendar' );
}); 