<?php

$app->group('/bringtpay', function() use($app) {

    /***************************************************************************
     * GET 'bringtpay'
     *
     * 
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $School = new School($this);
        $Room = new Room($this);
		
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Bringt pay';
        
        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        
        return $this->view->render($res, 'bringtpay.html', $view);
    })->setName('bringtpay');

    
});
