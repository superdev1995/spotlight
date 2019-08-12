<?php
/**
 * Created by PhpStorm.
 * User: ilia@m52studios.com
 */

use Carbon\Carbon;

$app->group('/planning', function() use($app) {
	/***************************************************************************
	 * GET 'planning/'
	 *
	 * View observation checklist
	 **************************************************************************/
	$this->get( '', function ( $req, $res, $args ) use ( $app ) {
		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Planning';

		return $this->view->render( $res, 'plansComingSoon.html', $view );
	})->setName( 'plans' );
});