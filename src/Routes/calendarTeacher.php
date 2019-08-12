<?php
/**
 * http://git.localhost/plans/calendar
 */

$app->group('/plans/calendar', function() use($app) {
    #With this we load the calendar
    /***************************************************************************
     * GET '
     *
     * View calendar
     **************************************************************************/
	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Calendar';
        
		return $this->view->render( $res, 'calendarTeachers.html', $view );
    })->setName( 'calendar' );

    /***************************************************************************
     * Get '/events'
     *
     * Load the events for show in the calendar
     **************************************************************************/
    $this->get( '/events', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Calendar';
        $calendar = new calendarEvents($this);

        $idUser=$req->getAttribute('user_id');
        $events = $calendar->getAllEvents($idUser);

        return json_encode($events);
        
    })->setName( 'events' );

    /***************************************************************************
     * POST ''
     *
     * create, edit and delete the events
     **************************************************************************/
    $this->post( '', function ( $req, $res, $args ) use ( $app ) {
        $Child = new Child($this);
        $calendar = new calendarEvents($this);

		$view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Calendar';

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
       
        #With this we see if we want add and event, collect the data and create it
        if(isset($_REQUEST["addEvent"])){
            $idUser=$req->getAttribute('user_id');

            $titleEvent=$_REQUEST["titleEvent"];
            $startDate=$_REQUEST["startDate"];
            $startHour=$_REQUEST["startHour"];
            $finishDate=$_REQUEST["finishDate"];
            $finishHour=$_REQUEST["finishHour"];
            $descriptionEvent=$_REQUEST["descriptionEvent"];
            $colorEvent=$_REQUEST["colorEvent"];
            $textColorEvent=$_REQUEST["textColorEvent"];
            $startEvent="$startDate $startHour";
            $finishEvent="$finishDate $finishHour";
            if(isset($_REQUEST["shareParents"])){
                $shareParents=$_REQUEST["shareParents"];
            }

            $view["errors"]="";
            if(empty($titleEvent)){
                $view["errors"]="The title can't be empty";
            }
            if(empty($startDate)){
                $view["errors"]="The start can't be empty";
            }else{
                $startComp=strtotime($startEvent);
                $endComp=strtotime($finishEvent);
                if($endComp<$startComp){
                    $view["errors"]="The event can't finish before start";
                }
                if(empty($finishDate)){
                    $startArray = explode(" ", $startEvent);
                    $finishEvent=$startArray[0]." 00:00:01";
                }
                if($startEvent==$finishEvent){
                    $endArray = explode(" ", $finishEvent);
                    $finishEvent=$endArray[0]." 00:00:01";
                }
            }
            if(!empty($shareParents)){
                $shareAllParents=1;
            }else{
                $shareAllParents=0;
            }

            if(empty($view["errors"])){
                $lastInsertId = $calendar->createEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$textColorEvent,$idUser,$shareAllParents);
            }

            #Now we see if we have to share te event with all the parents
            if(isset($_REQUEST["shareParents"])){
                if($shareParents="shareParents"){
                    $parents = $Child->getAllParentsForSchool($_SESSION['school_id']);
                    if(!empty($parents)){
                        foreach($parents as $parent){
                            $idParent=$parent->user_id;
                            $createEvent = $calendar->createParentEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idUser,$idParent,$lastInsertId);
                        }
                    }else{
                        $view["errors"]="You don't have parents for share the event";
                    }
                }
            }
            
        }

        #If the button of delete is pressed we select the event and delete it
        if(isset($_REQUEST["delEvent"])){

            $idEvent=$_REQUEST["idEvent"];

            $deleteEvent = $calendar->delEvent($idEvent);
            $deleteParentEvent=$calendar->delAllParentsEvent($idEvent);
        }
        
        #we comprove if the botton of edit was pressed and we do the change in the database
        if(isset($_REQUEST["editEvent"])){
            $idEvent=$_REQUEST["idEvent"];
            $titleEvent=$_REQUEST["titleEvent"];
            $startDate=$_REQUEST["startDate"];
            $startHour=$_REQUEST["startHour"];
            $finishDate=$_REQUEST["finishDate"];
            $finishHour=$_REQUEST["finishHour"];
            $descriptionEvent=$_REQUEST["descriptionEvent"];
            $colorEvent=$_REQUEST["colorEvent"];
            $textColorEvent=$_REQUEST["textColorEvent"];
            
            if(isset($_REQUEST["shareParents"])){
                $shareParents=$_REQUEST["shareParents"];
            }
            
            $startEvent="$startDate $startHour";
            $finishEvent="$finishDate $finishHour";

            $view["errors"]="";
            if(empty($titleEvent)){
                $view["errors"]="The title can't be empty";
            }
            if(empty($startDate)){
                $view["errors"]="The start can't be empty";
            }else{
                $startComp=strtotime($startEvent);
                $endComp=strtotime($finishEvent);
                if($endComp<$startComp){
                    $view["errors"]="The event can't finish before start";
                }
                if(empty($finishDate)){
                    $startArray = explode(" ", $startEvent);
                    $finishEvent=$startArray[0]." 00:00:01";
                }
                if($startEvent==$finishEvent){
                    $endArray = explode(" ", $finishEvent);
                    $finishEvent=$endArray[0]." 00:00:01";
                }
            }
            if(!empty($shareParents)){
                $shareAllParents=1;
            }else{
                $shareAllParents=0;
            }

            if(empty($view["errors"])){
                $editEvent = $calendar->updateEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$textColorEvent,$idEvent,$shareAllParents);
                if($shareAllParents){
                    $shareBefore=$calendar->getAllParentEvent($idEvent);
                    if($shareBefore){
                        $editEventAllParents=$calendar->updateEventAllParents($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idEvent);
                    }else{
                        $idUser=$req->getAttribute('user_id');
                        $parents = $Child->getAllParentsForSchool($_SESSION['school_id']);
                        if(!empty($parents)){
                            foreach($parents as $parent){
                                $idParent=$parent->user_id;
                                $createEvent = $calendar->createParentEvent($titleEvent,$startEvent,$finishEvent,$descriptionEvent,$colorEvent,$idUser,$idParent,$idEvent);
                            }
                        }else{
                            $view["errors"]="You don't have parents for share the event";
                        }
                     }//End sahreBefore
                }else{
                    $deleteParentEvent=$calendar->delAllParentsEvent($idEvent);
                }
                
            }

        }

        return $this->view->render( $res, 'calendarTeachers.html', $view );
    })->setName( 'editEvent' );
    
    /***************************************************************************
     * GET '/dropEvent'
     *
     * create, edit and delete the events
     **************************************************************************/
    $this->get( '/dropEvent', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Calendar';
        $calendar = new calendarEvents($this);

        $idEvent=$_REQUEST["id"];
        $startEvent=$_REQUEST["start"];
        $finishEvent=$_REQUEST["end"];
        if(empty($startEvent)){
            $view["errors"]="The start can't be empty";
        }else{
            $startComp=strtotime($startEvent);
            $endComp=strtotime($finishEvent);
            if($endComp<$startComp){
                $view["errors"]="The event can't finish before start";
            }
            if(empty($finishEvent)){
                $startArray = explode(" ", $startEvent);
                $finishEvent=$startArray[0]." 00:00:01";
            }
            if($startEvent==$finishEvent){
                $endArray = explode(" ", $finishEvent);
                $finishEvent=$endArray[0]." 00:00:01";
            }
        }

        if(empty($view["errors"])){
            $resp = $calendar->dropEvent($startEvent,$finishEvent,$idEvent);
            $shareBefore=$calendar->getAllParentEvent($idEvent);
            if($shareBefore){
                $dropEventAllParents=$calendar->dropEventAllParents($startEvent,$finishEvent,$idEvent);
            }
        }

        return $this->view->render( $res, 'calendarTeachers.html', $view );

    })->setName( 'dropEvent' );

});