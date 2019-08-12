<?php
$app->group('/parentEvents', function() use($app) {
    #Code for indicate the route for show the events shared with the parents
	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Events';
        $calendar = new calendarEvents($this);

        $idUser=$req->getAttribute('user_id');
        $view["eventsParent"] = $calendar->getAllParentsEvents($idUser);

		return $this->view->render( $res, 'parentEvents.html', $view );
    })->setName( 'parentEvents' );
   
});

