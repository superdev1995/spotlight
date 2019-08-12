<?php


$app->group('/plans', function() use($app) {
    
	$this->get( '/list', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
		$School = new School($this);
        $Child = new Child($this);

		$view['title'] = "Plans";
		$view['flash'] = $this->flash->getMessages();
        
        $children = $Child->getAssociatedChildren( $req->getAttribute( 'user_id' ) );

		//if only one child assigned - redirect to his/her plans
		if ( count( $children ) == 1 ) {
			return $res->withStatus( 302 )->withRedirect( $this->router->pathFor( 'plansForChild', [ 'child_id' => $children[0]->child_id ] ) );
		}

		$view['children'] = $children;
        
        return $this->view->render( $res, 'plansSelectChild.html', $view );
	})->setName( 'plansParent' );
    
    $this->get( '/list/{child_id}', function ( $req, $res, $args ) use ( $app ) {

		$Child = new Child($this);
        $Plan = new Plan($this);

		$monthly_plans = $Plan->getAllMonthlyForParent( $args['child_id'] );
		$weekly_plans = $Plan->getAllWeeklyForParent( $args['child_id'] );
		$daily_plans = $Plan->getAllDailyForParent( $args['child_id'] );
		$child_details = $Child->getOne($args['child_id']);

		$view = [
			'title' => 'Plans for '.$child_details->child_name,
            'child' => $child_details,
            'monthly_plans' => $monthly_plans,
            'weekly_plans' => $weekly_plans,
            'daily_plans' => $daily_plans
		];

		return $this->view->render( $res, 'plans.html', $view );
	} )->setName( 'plansForChild' );
    
});