<?php

use \Carbon\Carbon;

$app->group('/revenue', function() use($app) {
    /***************************************************************************
     * GET 'revenue'
     *
     * View all the billis
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $view['title'] = 'Revenue';
        $fee = new Fee($this);
        if (isset($_COOKIE['auth_token'])) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        $view['billing']=$fee->selectAllBilling($token->user_id);
        switch (Carbon::now()->month) {
                case 1:
                    $month="Jan";
                    break;
                case 2:
                    $month="Feb";
                    break;
                case 3:
                    $month="Mar";
                    break;
                case 4:
                    $month="Apr";
                    break;
                case 5:
                    $month="May";
                    break;
                case 6:
                    $month="Jun";
                    break;
                case 7:
                    $month="Jul";
                    break;
                case 8:
                    $month="Aug";
                    break;
                case 9:
                    $month="Sep";
                    break;
                case 10:
                    $month="Oct";
                    break;
                case 11:
                    $month="Nov";
                    break;
                case 12:
                    $month="Dec";
                    break;
        }
        $view['graphics']['month']=$month;
        $view['graphics']['month_n']='0'.Carbon::now()->month;
        $view['graphics']['year_n']=Carbon::now()->year;
        $view['graphics']['year']=Carbon::now()->year;
        return $this->view->render($res, 'revenue.html', $view);
    })->setName('revenue');
    /***************************************************************************
     * POST 'revenue'
     *
     * Change the graphics
     **************************************************************************/
    $this->post('', function($req, $res, $args) use($app) {
        $data=$req->getParsedBody();
        $view['title'] = 'Revenue';
        $fee = new Fee($this);
        if (isset($_COOKIE['auth_token'])) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        $view['billing']=$fee->selectAllBilling($token->user_id);
        if (isset($data['month'])){
                $view['graphics']['month']=$data['month'];
                switch ($data['month']) {
                        case "Jan":
                            $month_n='01';
                            break;
                        case "Feb":
                            $month_n='02';
                            break;
                        case "Mar":
                            $month_n='03';
                            break;
                        case "Apr":
                            $month_n='04';
                            break;
                        case "May":
                            $month_n='05';
                            break;
                        case "Jun":
                            $month_n='06';
                            break;
                        case "Jul":
                            $month_n='07';
                            break;
                        case "Aug":
                            $month_n='08';
                            break;
                        case "Sep":
                            $month_n='09';
                            break;
                        case "Oct":
                            $month_n='10';
                            break;
                        case "Nov":
                            $month_n='11';
                            break;
                        case "Dec":
                            $month_n='12';
                            break;
                }
            }else{
            $month_n='0'.Carbon::now()->month;
            switch (Carbon::now()->month) {
                case 1:
                    $month="Jan";
                    break;
                case 2:
                    $month="Feb";
                    break;
                case 3:
                    $month="Mar";
                    break;
                case 4:
                    $month="Apr";
                    break;
                case 5:
                    $month="May";
                    break;
                case 6:
                    $month="Jun";
                    break;
                case 7:
                    $month="Jul";
                    break;
                case 8:
                    $month="Aug";
                    break;
                case 9:
                    $month="Sep";
                    break;
                case 10:
                    $month="Oct";
                    break;
                case 11:
                    $month="Nov";
                    break;
                case 12:
                    $month="Dec";
                    break;
        }
        $view['graphics']['month']=$month;
            }
        $view['graphics']['month_n']=$month_n;
        $view['graphics']['year_n']=Carbon::now()->year;
        if (isset($data['year'])){
            $view['graphics']['year']=$data['year'];
        }else{
            $view['graphics']['year']=Carbon::now()->year;
        }
        return $this->view->render($res, 'revenue.html', $view);
    });
    /***************************************************************************
     * GET 'revenue/?/?/?'
     *
     * View the billing detail
     **************************************************************************/
    $this->get('/{child_id}/{teacher_id}/{date}', function($req, $res, $args) use($app) {
        $view['title'] = 'Bill';
        $fee = new Fee($this);
        $view['billing']=$fee->selectBilling($args["teacher_id"],$args["child_id"],$args["date"]);
        $view['info']=$fee->selectBillingOne($args["teacher_id"],$args["child_id"],$args["date"]);
        return $this->view->render($res, 'showBill.html', $view);
    });
     /***************************************************************************
     * GET 'revenue/paid/?/?/?'
     *
     * Change the status of billing
     **************************************************************************/
    $this->get('/paid/{child_id}/{teacher_id}/{date}', function($req, $res, $args) use($app) {
        $fee = new Fee($this);
        $fee->paid($args["teacher_id"],$args["child_id"],$args["date"]);
         $data=$req->getParsedBody();
        $view['title'] = 'Revenue';
        if (isset($_COOKIE['auth_token'])) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        $view['billing']=$fee->selectAllBilling($token->user_id);
        if (isset($data['month'])){
                $view['graphics']['month']=$data['month'];
                switch ($data['month']) {
                        case "Jan":
                            $month_n='01';
                            break;
                        case "Feb":
                            $month_n='02';
                            break;
                        case "Mar":
                            $month_n='03';
                            break;
                        case "Apr":
                            $month_n='04';
                            break;
                        case "May":
                            $month_n='05';
                            break;
                        case "Jun":
                            $month_n='06';
                            break;
                        case "Jul":
                            $month_n='07';
                            break;
                        case "Aug":
                            $month_n='08';
                            break;
                        case "Sep":
                            $month_n='09';
                            break;
                        case "Oct":
                            $month_n='10';
                            break;
                        case "Nov":
                            $month_n='11';
                            break;
                        case "Dec":
                            $month_n='12';
                            break;
                }
            }else{
            $month_n='0'.Carbon::now()->month;
            switch (Carbon::now()->month) {
                case 1:
                    $month="Jan";
                    break;
                case 2:
                    $month="Feb";
                    break;
                case 3:
                    $month="Mar";
                    break;
                case 4:
                    $month="Apr";
                    break;
                case 5:
                    $month="May";
                    break;
                case 6:
                    $month="Jun";
                    break;
                case 7:
                    $month="Jul";
                    break;
                case 8:
                    $month="Aug";
                    break;
                case 9:
                    $month="Sep";
                    break;
                case 10:
                    $month="Oct";
                    break;
                case 11:
                    $month="Nov";
                    break;
                case 12:
                    $month="Dec";
                    break;
        }
        $view['graphics']['month']=$month;
            }
        $view['graphics']['month_n']=$month_n;
        $view['graphics']['year_n']=Carbon::now()->year;
        if (isset($data['year'])){
            $view['graphics']['year']=$data['year'];
        }else{
            $view['graphics']['year']=Carbon::now()->year;
        }
        return $this->view->render($res, 'revenue.html', $view);
    });
     /***************************************************************************
     * GET 'revenue/paid/?/?/?'
     *
     * Change the status of billing
     **************************************************************************/
    $this->get('/parent/{child_id}/{teacher_id}/{date}', function($req, $res, $args) use($app) {
        $view['title'] = 'Bill';
        $fee = new Fee($this);
        $fee->show($args["teacher_id"],$args["child_id"],$args["date"]);
        $view['billing']=$fee->selectBilling($args["teacher_id"],$args["child_id"],$args["date"]);
        $view['info']=$fee->selectBillingOne($args["teacher_id"],$args["child_id"],$args["date"]);
        return $this->view->render($res, 'showBill.html', $view);
    });
     /***************************************************************************
     * POST 'revenue/mail/?/?/?'
     *
     * Send a mail to the parents
     **************************************************************************/
    $this->post('/mail/{child_id}/{teacher_id}/{date}', function($req, $res, $args) use($app) {
        $view['title'] = 'Bill';
        $fee = new Fee($this);
        $fee->show($args["teacher_id"],$args["child_id"],$args["date"]);
        $parents=$fee->parentEmail($args["child_id"]);
        if ($parents != NULL){
            foreach ($parents as $parent) {
                $this->mailer->send('billAnnounce.html', [
                        'to' => $parent->user_email,
                        'subject' => 'Bill',
                        'first_name' => $parent->user_first_name,
                        'data' => $data
                    ]);
            }
                $this->logger->info('Announced Bill .', [ 'email', $user->user_email ]);
            }
        $view['billing']=$fee->selectBilling($args["teacher_id"],$args["child_id"],$args["date"]);
        $view['info']=$fee->selectBillingOne($args["teacher_id"],$args["child_id"],$args["date"]);
        return $this->view->render($res, 'showBill.html', $view);
    });
});
