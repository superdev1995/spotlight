<?php

require_once('./../vendor/autoload.php');

$app->group('/paymentManagement', function () use ($app) {
    $this->get('', function ($req, $res, $args) use ($app) {
        $Country = new Country($this);
        $School = new School($this);

        $school = $School->getOne($_SESSION['school_id']);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Pre-School Payment';

        $view['activeschool']=$_SESSION['school_id'];

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        $user = $req->getAttribute('user');
        $idUser = $user->user_id;
        $admin = $School->checkIfAdmin($idUser);

        if($admin[0]->role == '1'){
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);

        }else{
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));

        }

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }


        foreach($admin as $ad){
            $school=$School->getOne($ad->school_id);
            $view['listschool'][$ad->school_id]=$school;
            $view['balance'][$ad->school_id]=null;
            if($school->stripe_connect_id != "") {

                \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

                $balance = \Stripe\Balance::retrieve(
                    ["stripe_account" => $school->stripe_connect_id]
                );

                $balanceAll = \Stripe\BalanceTransaction::all(["type" => 'charge'], [
                    "stripe_account" => $school->stripe_connect_id,
                ]);

                $payout = \Stripe\Payout::all([], [
                    "stripe_account" => $school->stripe_connect_id,
                ]);

                $fee = \Stripe\ApplicationFee::all();


                //$stripeAccountObj = \Stripe\Account::retrieve($school->stripe_connect_id);

                //var_dump($balance);

                if ($balance['pending'][0]['currency'] == 'eur') {
                    $currency = '€';
                } else {
                    $currency = '$';
                }

                $charge = \Stripe\Charge::all([], [
                    'api_key' => $this->get('settings')['stripe']['secretKey'],
                    'stripe_account' => $school->stripe_connect_id
                ]);

                $charge1 = json_decode($charge->__toJSON(), true);
                $payout1 = json_decode($payout->__toJSON(), true);
                $balanceAll1 = json_decode($balanceAll->__toJSON(), true);
                $fee1 = json_decode($fee->__toJSON(), true);

                //var_dump($balanceAll1);

                $view['fee'][$ad->school_id] = $fee1;
                $view['payout'][$ad->school_id] = $payout1;
                $view['balanceAll'][$ad->school_id] = $balanceAll1;
                $view['currencyB'][$ad->school_id] = $currency;
                $view['balance'][$ad->school_id] = $balance;
                $view['charge'][$ad->school_id] = $charge1;
                // $view['fee'] = $fee1;
                // $view['payout'] = $payout1;
                // $view['balanceAll'] = $balanceAll1;
                // $view['currencyB'] = $currency;
                // $view['balance'] = $balance;
                // $view['charge'] = $charge1;
            }

            $view['schools'][$ad->school_id] = $school;
            $view['schoolUS'][$ad->school_id] = $School->getOneUS($_SESSION['school_id']);
            $view['categories'][$ad->school_id] = $School->getCategories();
            $view['countries'][$ad->school_id] = $Country->getCountries();

            // $view['school'] = $school;
            // $view['schoolUS'] = $School->getOneUS($_SESSION['school_id']);
            // $view['categories']= $School->getCategories();
            // $view['countries'] = $Country->getCountries();
        }


        
         //var_dump($view['balance']);
        return $this->view->render($res, 'paymentManagement.html', $view);

    })->setName('paymentManagement');



    $this->get('/{charge_id}', function($req, $res, $args) use($app) {

        $School = new School($this);
        $school = $School->getOne($_SESSION['school_id']);

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);
        //var_dump($args['charge_id']);

            //$account = \Stripe\Account::create($school->stripe_connect_id);

            $stripeConnectId = $school->stripe_connect_id;

        try {
            \Stripe\Refund::create([
                "charge" => $args['charge_id'],
            ], ["stripe_account" => $stripeConnectId
            ]);

            $this->logger->error('Refund Stripe customer with success.');
            $this->flash->addMessage('success', 'Refund Stripe customer with success.');
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));

        }catch(Exception $e) {
            //var_dump($re);
            $this->logger->error('Impossible to Refund Stripe customer.');
            $this->flash->addMessage('danger', 'Refund Stripe customer with success.');
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

    })->setName('chargeRefund');


});
