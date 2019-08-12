<?php
    $hosturl = str_replace('public','',$_SERVER['DOCUMENT_ROOT']);
    require_once $hosturl.'/vendor/autoload.php';
    use Dompdf\Dompdf as Dompdf;


$app->group('/invoices', function() use($app) {

    /***************************************************************************
     * GET 'invoice new '
     *
     * View billing form
     **************************************************************************/
  
    $this->get('/[{child_id}]', function($req, $res, $args) use($app) {
        $view['title'] = 'Create a bill';
        $Child = new Child($this);
        $invoice = new Invoice($this);
        $School = new School($this);
        $Room = new Room($this);

        $child = $Child->getOneMore($args['child_id']);
        $Bus = $School->getOne($child->school_id);
        $view['school'] = $Bus;
        $country_id =$child->country_id;
        $data['business_name'] = $Bus->school_name;
        $data['business_city'] = $Bus->school_city;
        $data['business_PC'] = $Bus->school_postal_code;
        $data['business_phone'] = $Bus->school_phone;
        $data['business_email'] = $School->getAdminEmail($child->school_id);
        $view['country_id'] = $country_id;

        if($country_id == 'IE' )
        {
            $sjdata =  json_encode($invoice->selectSchemeInfo());
            $view['scheme_data']=$sjdata;
        }
        else
        {
            $view['scheme_data']='';
        }

        $date = $invoice->day();
        $view['child'] = $child;
        $view['date_from'] = $date;
        $view['date_to'] = $date;
        $view['date'] = $date;
        $view['invoiceno'] = $invoice->selectInvoiceNo($args['child_id']);
       
        if ($_COOKIE['auth_token']) 
        {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
     
        $view['previous']=$invoice->selectBillingPrevious($token->user_id,$args['child_id'] ,$view['date']);

        $view['rooms'] = $Room->getAll($_SESSION['school_id']);
        $view['children'] = $Child->getAll($_SESSION['school_id']);

        return $this->view->render($res, 'invoices.html', $view);
    })->setName('invoices');


    /***************************************************************************
     * GET 'invoice list for teacher and parent '
     *
     * View billing form
     **************************************************************************/

    $this->get('/list/{child_id}', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);
        $School = new School($this);
        $view['title'] = 'Billing';
        $view['flash'] = $this->flash->getMessages();
        $view['year'] = date("Y");
        $user = $req->getAttribute('user');
        $teacher_id = $user->user_id;
        //$test = $invoice->selectAllBilling($);

        $child = $Child->getOneMore($args['child_id']);
        $Bus = $School->getOne($child->school_id);
        $admin = $School->getAdmins($child->school_id);

        //print_r($invoice);

        if ($user->user_id == $admin[0]->user_id ) {
            $billing = $invoice->selectBillingAll($args['child_id'],$teacher_id);
        }elseif($user->user_type == "T") {
            $billing = $invoice->selectBillingTeacher($args['child_id'], $teacher_id);
        }else{
            $billing = $invoice->selectBillingParent($args['child_id']);
        }



        $country_id =$child->country_id;


        $view['school'] = $Bus;
        $view['billing'] = $billing;
        $view['child'] = $child;
        $view['admin'] = $admin[0];
        //$view['teacher'] = $School->getAll($test);
        //var_dump($_SESSION);
        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        $data['business_name'] = $Bus->school_name;
        $data['business_city'] = $Bus->school_city;
        $data['business_PC'] = $Bus->school_postal_code;
        $data['business_phone'] = $Bus->school_phone;
        $data['business_email'] = $School->getAdminEmail($child->school_id);
        $view['country_id'] = $country_id;
        if($country_id == 'IE' )
        {
            $sjdata =  json_encode($invoice->selectSchemeInfo());
            $view['scheme_data']=$sjdata;
        }
        else
        {
            $view['scheme_data']='';
        }
        $retinvoice =$invoice->selectInvoice($child->school_id,$args['id']);
        $view['invoice_id']  = $retinvoice->id;
        $view['data']=$retinvoice;
        $now = new DateTime();
        $date = $now->format('Y-m-d H:i:s');
        $date_from = new DateTime($retinvoice->date_from);
        $date_to = new DateTime($retinvoice->date_to);
        $view['child'] = $child;
        $view['date_from'] = $date_from->format('Y-m-d');
        $view['date_to'] = $date_to->format('Y-m-d');
        $view['date'] = $date;
        $view['invoiceno'] = $args['id'];
        $view['status'] = $retinvoice->status;
        if ($_COOKIE['auth_token'])
        {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        if($token->user_type == "T" && $token->user_admin == "1" )
        {
            $view['isadmin'] = '1';
        }
        else
        {
            $view['isadmin'] = '0';
        }

        return $this->view->render($res, 'invoice_list.html', $view);
    })->setName('invoice_list');


    /***************************************************************************
     * GET 'invoice page for parent '
     *
     * View billing & button paiement
     **************************************************************************/

    $this->get('/paypage/{child_id}/{id}', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);
        $School = new School($this);
        $view['title'] = 'Pay a bill';
        $user = $req->getAttribute('user');
        $teacher_id = $user->user_id;

        $child = $Child->getOneMore($args['child_id']);
        $billing = $invoice->selectBillingTeacher($args['child_id'], $teacher_id);
        $thisinvoice = $invoice->selectInvoiceOne($args['child_id'], $args['id']);

        $country_id =$child->country_id;
        $Bus = $School->getOne($child->school_id);
        $view['school'] = $Bus;
        $view['billing'] = $billing;
        $view['child'] = $child;

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        //var_dump($req->getAttribute('user_id'));


        $user_type = $user->user_type;
        $admin = $user->user_admin;

        /*if ($user_type != 'P' AND $admin == 0) {
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');
            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }*/

        $data['business_name'] = $Bus->school_name;
        $data['business_city'] = $Bus->school_city;
        $data['business_PC'] = $Bus->school_postal_code;
        $data['business_phone'] = $Bus->school_phone;
        $data['business_email'] = $School->getAdminEmail($child->school_id);
        $view['country_id'] = $country_id;

        $year = date('Y');

        for ($i = $year; $i <= $year + 15; $i++) {
            $view['years'][] = $i;
        }

        if($country_id == 'IE' ) {
            $sjdata =  json_encode($invoice->selectSchemeInfo());
            $view['scheme_data']=$sjdata;
        }else{
            $view['scheme_data']='';
        }

        $retinvoice =$invoice->selectInvoice($child->school_id,$args['id']);
        $view['thisinvoice'] = $thisinvoice;

        $view['invoice_id']  = $retinvoice->id;
        $view['data']=$retinvoice;
        $now = new DateTime();
        $date = $now->format('Y-m-d H:i:s');
        $date_from = new DateTime($retinvoice->date_from);
        $date_to = new DateTime($retinvoice->date_to);
        $view['child'] = $child;
        $view['date_from'] = $date_from->format('Y-m-d');
        $view['date_to'] = $date_to->format('Y-m-d');
        $view['date'] = $date;
        $view['invoiceno'] = $args['id'];
        $view['status'] = $retinvoice->status;
        if($_COOKIE['auth_token']){
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        if($token->user_type == "T" && $token->user_admin == "1" ) {
            $view['isadmin'] = '1';

        }else{
            $view['isadmin'] = '0';
        }


        return $this->view->render($res, 'invoice_paypage.html', $view);
    })->setName('invoice_paypage');



      /***************************************************************************
     * GET 'invoice load'
     *
     * View billing form
     **************************************************************************/
    $this->get('/{child_id}/{id}', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);
        $School = new School($this);
        $view['title'] = 'Edit a bill';

        $child = $Child->getOneMore($args['child_id']);
        $country_id =$child->country_id;
        $Bus = $School->getOne($child->school_id);
        $view['school'] = $Bus;

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        $data['business_name'] = $Bus->school_name;
        $data['business_city'] = $Bus->school_city;
        $data['business_PC'] = $Bus->school_postal_code;
        $data['business_phone'] = $Bus->school_phone;
        $data['business_email'] = $School->getAdminEmail($child->school_id);
        $view['country_id'] = $country_id;
        if($country_id == 'IE' ){
            $sjdata =  json_encode($invoice->selectSchemeInfo());
            $view['scheme_data']=$sjdata;
        }else{
            $view['scheme_data']='';
        }
        $retinvoice =$invoice->selectInvoice($child->school_id,$args['id']);
        $extinvoice = $invoice->selectExtrasInvoice($args['id'], $args['child_id']);


        $view['id'] = $retinvoice->invoice_id;
        $view['invoice_id']  = $retinvoice->id;
        $view['data']=$retinvoice;
        $view['data_extras'] = $extinvoice;
        $now = new DateTime();
        $date = $now->format('Y-m-d H:i:s');
        $date_from = new DateTime($retinvoice->date_from);
        $date_to = new DateTime($retinvoice->date_to);
        $view['child'] = $child;
        $view['date_from'] = $date_from->format('Y-m-d');
        $view['date_to'] = $date_to->format('Y-m-d');
        $view['date'] = $date;
        $view['invoiceno'] = $args['id'];
        $view['status'] = $retinvoice->status;
        if ($_COOKIE['auth_token']) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        if($token->user_type == "T" && $token->user_admin == "1" ){
            $view['isadmin'] = '1';
        }else{
            $view['isadmin'] = '0';
        }

        $user = $req->getAttribute('user');
        $user_type = $user->user_type;

        if ($user_type != 'T') {
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');
            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        return $this->view->render($res, 'invoice_edit.html', $view);
    })->setName('invoice_load');


     /***************************************************************************
     * GET 'invoice print'
     *
     * Print invoice pdf
     **************************************************************************/
    $this->get('/print/{child_id}/{id}', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);
        $School = new School($this);
        $view['title'] = 'Billing';
     
        $child = $Child->getOneMore($args['child_id']);
        $country_id =$child->country_id;
        $Bus = $School->getOne($child->school_id);
        $view['school'] = $Bus;

        $view['country_id'] = $country_id;
       
        $retinvoice = $invoice->selectInvoice($child->school_id,$args['id']);
        $extinvoice = $invoice->selectExtrasInvoice($args['id'], $args['child_id']);
        $view['data']=$retinvoice;
        $view['data_extras']=$extinvoice;
        $now = new DateTime();
        $date = $now->format('Y-m-d H:i:s');
        $date_from = new DateTime($retinvoice->date_from);
        $date_to = new DateTime($retinvoice->date_to);
        $view['child'] = $child;
        $view['date_from'] = $date_from->format('Y-m-d');
        $view['date_to'] = $date_to->format('Y-m-d');
        $view['date'] = $date;
        $view['invoiceno'] = $args['id'];
        $view['status'] = $retinvoice->status;
        if($_COOKIE['auth_token']){
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }

        $dompdf = new Dompdf();
        $dompdf->set_option('defaultFont', 'Courier');
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled',true);

        $content = $this->view->fetch('invoice_print.html', $view);

       $dompdf->loadHtml($content);
       $dompdf->setPaper('A4', 'portrait');
       $dompdf->render();

       $data = $req->getParsedBody();

       $file_name = 'invoice_'.$child->child_name.'('.$child->child_id.')_no_'.$retinvoice->invoiceNumber.'.pdf';
       $out = $dompdf->output($file_name);
       file_put_contents('invoices/'.$file_name, $out);
       
       return $res->withStatus(200)
       ->withHeader('Content-Type', 'application/pdf')
       ->write($out);
    })->setName('invoice_print');

    /***************************************************************************
     * POST 'invoices'
     *
     * Save the information of the billing
     **************************************************************************/
    $this->post('/{child_id}/{id}', function($req, $res, $args) use($app) {
        $data = $req->getParsedBody();
        $view['title'] = 'Billing';
        $Child = new Child($this);
        $School = new School($this);
        $Bus = $School->getOne($_SESSION['school_id']);
        $data['business_name'] = $Bus->school_name;
        $data['business_city'] = $Bus->school_city;
        $data['business_PC'] = $Bus->school_postal_code;
        $data['business_phone'] = $Bus->school_phone;
        $data['business_email'] = $School->getAdminEmail($_SESSION['school_id']);
        $now = new DateTime();
        $data['date'] = $now->format('Y-m-d H:i:s');

        $invoice = new Invoice($this);
        $child = $Child->getOneMore($args['child_id']);
        $country_id =$child->country_id;
        $view['country_id'] = $country_id;
        
        if($country_id == 'IE' ){
            $sjdata =  json_encode($invoice->selectSchemeInfo());
            $view['scheme_data']=$sjdata;  
        }else{
            $view['scheme_data']='';
        }
        $date = $invoice->day();
        $view['child'] = $child;
        $date_from = new DateTime($data['date_from']);
        $date_to = new DateTime($data['date_to']);
        $view['date_from'] = $date_from->format('Y-m-d');
        $view['date_to'] = $date_to->format('Y-m-d');
        $view['date'] = $date;
        $view['invoiceno'] = $args['id'];
       
        if ($_COOKIE['auth_token']) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        if($token->user_type == "T" && $token->user_admin == "1" ) {
            $view['status'] = 'pending';
            $view['isadmin'] = '1';

        }else{
            $view['status'] = 'pending';
            $view['isadmin'] = '0';
        }


        $invoice_id = $invoice->checkExistsId($args['child_id'],$args['id']);
        $extras = $invoice->selectExtrasInvoice($args['id'], $args['child_id']);
        if($invoice_id != null){
            $invoice->update($data,$invoice_id) ;
            $invoice->update_inv_scheme($data,$invoice_id);
            $invoice->updateExtras($data);
            $invoice->insertExtras2($data, $args['id'], $args['child_id']);
        }else{
            $invoice_id = $invoice->create($data);
            $invoice->invoicescheme($data,$invoice_id);
            $invoice->insertExtras($data, $args['id'], $args['child_id']);

        }
        $view['invoice_id']  = $invoice_id; 
       
        if($data['validate']="yes" && $data['approved']="yes"){
            $retinvoice =$invoice->selectInvoice($child->school_id,$args['id']);

            foreach ($Child->getParents($args['child_id']) as $parent) {
                if (!$parent->user_notify_record) {
                    continue;
                }
    
                $this->mailer->send('payInvoice.html', [
                    'to' => $parent->user_email,
                    'subject' => 'A new invoice has been created for your child',
                    'first_name' => $parent->user_first_name,
                    'user' => $req->getAttribute('user'),
                    'child' => $child,
                    'thisinvoice' => $retinvoice,
                ]);
    
                $this->logger->info('Notification sent.', [ 'email' => $parent->user_email ]);
            }
        }
        // $parents=$invoice->parentEmail($data['id_child']);
        // if ($parents != NULL){
        //     foreach ($parents as $parent) {
        //         $this->mailer->send('billAnnounce.html', [
        //                 'to' => $parent->user_email,
        //                 'subject' => 'Bill',
        //                 'first_name' => $parent->user_first_name,
        //                 'data' => $data
        //             ]);
        //     }
        //         $this->logger->info('Announced Bill .', [ 'email', $user->user_email ]);
        // }
        $view['data']=$invoice->selectInvoice($child->school_id,$args['id']);
        $view['previous']=$invoice->selectBillingPrevious($token->user_id,$args['child_id'] ,$view['date']);

        if($data['addMessage'] == 0){
             $this->flash->addMessage('success', 'The Invoice was successful created.');
        }else {
            $this->flash->addMessage('success', 'The Invoice was successful updated.');
        }

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('invoice_list', ['child_id' => $data['id_child']]));

    });

    /***************************************************************************
     * Duplicate invoice
     *
     *
     **************************************************************************/

    $this->get( '/duplicate/{child_id}/{invoiceno}/{id}', function ( $req, $res, $args ) use ( $app ) {

        $invoice = new Invoice($this);
        $nbinvoice = $invoice->selectInvoiceNo($args['child_id']);
        $invoice->insertDuplicateInvoice($nbinvoice, $args['invoiceno'], $args['child_id']);
        $lastInsert = $invoice->getLastInvoice();
        var_dump($lastInsert);

        //$test = $invoice->selectInvoiceAndScheme($args['child_id'], $args['id']);
        //var_dump();

        $invoice->insertDuplicateInvoiceScheme($lastInsert->id, $args['id']);
        $invoice->insertDuplicateExtras($lastInsert->invoiceNumber, $args['invoiceno'], $args['child_id']);

        //var_dump($args['child_id']);
        //var_dump($args['invoiceno']);
        //var_dump($args['id']);
        //var_dump($lastInsert->id);
        //var_dump($lastInsert->invoiceNumber);

        $this->flash->addMessage('success', 'The invoice has been duplicated');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('invoice_list', ['child_id' => $args['child_id']]));
    })->setName('duplicate');


    /***************************************************************************
     * POST 'billing/payment'
     *
     * Create a new payment for parent with Stripe
     **************************************************************************/
    $this->post('/paymentBill', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);
        $School = new School($this);
        $User = new User($this);

        $data = $req->getParsedBody();
        $view['thisinvoicee'] = $data;
        $stripeConnect = $School->getOne($_SESSION['school_id']);
        $stripeConnectId = $stripeConnect->stripe_connect_id;

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);
        $parent = $req->getAttribute('user');

        //var_dump($_SESSION['school_id']);

        /**
         * Check whether a subscription has already been created with Stripe
         * before (indicated by an existing Stripe ID).
         */

        if ($parent->stripe_parent_id) {
            /**
             * Attempt to retrieve the Stripe account using the Stripe ID
             */
            try {
                $account = \Stripe\Customer::retrieve($parent->stripe_parent_id);

                $this->logger->debug('Stripe customer retrieved.', [ 'stripe_parent_id' => $account->id ]);
            } catch(Exception $e) {
                $this->logger->error('Could not update Stripe customer.', [ 'stripe_parent_id' => $account->id ]);
                $this->flash->addMessage('danger', 'Could not update your details.');

                return $res->withStatus(302)->withHeader('Location', 'list/'.$data['idC'].'');
            }
        } else {
            /**
             * Attempt to create a new Stripe account, since no Stripe ID exists
             */
            try {
                $account = \Stripe\Customer::create();

                $User->setStripeParentId($parent->user_id, $account->id);

                $this->logger->debug('Stripe customer created.', [ 'stripe_parent_id' => $account->id ]);
            } catch(Exception $e) {
                $this->logger->error('Could not create Stripe customer.', [ 'user_id' => $req->getAttribute('user_id') ]);
                $this->flash->addMessage('danger', 'Could not instantiate your billing details.');

                return $res->withStatus(302)->withHeader('Location', 'list/'.$data['idC'].'');
            }
        }

        if ($data['stripeParentToken']) {
            try {
                $account->source = $data['stripeParentToken'];
                $account->save();

                $this->logger->info('Customer card updated.', [ 'stripe_parent_id' => $account->id ]);
            } catch(\Stripe\Error\Card $e) {
                $this->logger->error($e);
            }
        }

        $cc = $data['currency'];

        $currency = array(
            "€" => "eur",
            "£" => "gbp",
            "$" => "usd",
            "A$" => "aud",
        );

        try {

           \Stripe\Charge::create([

                "amount" => $data['total']*100,
                "currency" => $currency[$cc],
                "description" => 'Billing for ' .$data['nameC'].'('.$data['idC'].'), Invoice n°'.$data['invoiceNb'].' From '.$data['dateFrom'].' To '.$data['dateTo'].'',
                "source" => "tok_visa",
                "application_fee" => round($data['total']),
                ], ["stripe_account" => $stripeConnectId

           ]);

           $invoice->setStatus($data['status'], $data['id']);

        } catch (\Exception $e) {

            $this->logger->error($e);
            $this->flash->addMessage('danger', 'test'.$e->getMessage());
            return $res->withStatus(302)->withHeader('Location', 'list/'.$data['idC'].'');
        }

        foreach ($School->getAdministrators($_SESSION['school_id']) as $administrator) {
            ListHandler::setSubscribed($administrator->user_email, 'Yes', $req->getAttribute('user')->user_type);
        }

        $this->flash->addMessage('success', 'We have received your payment from Invoice n°'.$data['invoiceNb'].'.');
        return $res->withStatus(302)->withHeader('Location', 'list/'.$data['idC'].'');

    })->setName('billingPayment');

     /***************************************************************************
     * Send email to parent
     *
     *
     **************************************************************************/

    $this->get('/sendMail/{child_id}/{id}', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);

        $child = $Child->getOneMore($args['child_id']);
        $retinvoice =$invoice->selectInvoice($child->school_id,$args['id']);

        $child = $Child->getOne($args['child_id']);

        $validate = "yes";
        $id = $retinvoice->invoice_id;

        $invoice->setValidate($validate, $id);

        foreach ($Child->getParents($args['child_id']) as $parent) {
            if (!$parent->user_notify_record) {
                continue;
            }

            $this->mailer->send('payInvoice.html', [
                'to' => $parent->user_email,
                'subject' => 'A new invoice has been created for your child',
                'first_name' => $parent->user_first_name,
                'user' => $req->getAttribute('user'),
                'child' => $child,
                'thisinvoice' => $retinvoice,
            ]);

            $this->logger->info('Notification sent.', [ 'email' => $parent->user_email ]);
        }

        $this->flash->addMessage('success', 'Invoice sent to parent, waiting for payment');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('invoice_list', ['child_id' => $retinvoice->idC]));

    })->setName('invoice_send');

    /***************************************************************************
     * Parent Send email to admin
     *
     *
     **************************************************************************/

    $this->get('/sendMailAdmin/{child_id}/{id}', function($req, $res, $args) use($app) {
        $invoice = new Invoice($this);
        $Child = new Child($this);
        $School = new School($this);
        $User = new User($this);


        $child = $Child->getOneMore($args['child_id']);
        $retinvoice =$invoice->selectInvoice($child->school_id,$args['id']);
        $user = $User->getOne($retinvoice->user_id);
        $child = $Child->getOne($args['child_id']);

        //$validate = "yes";
        //$id = $retinvoice->invoice_id;

        //$invoice->setValidate($validate, $id);



        foreach ($School->getAdministrators($_SESSION['school_id']) as $administrator) {
            if (!$administrator->user_notify_record) {
                continue;
            }

            $this->mailer->send('validateInvoice.html', [
                'to' => $administrator->user_email,
                'subject' => 'A new invoice has been created, a validation is required',
                'first_name' => $administrator->user_first_name,
                'user' => $req->getAttribute('user'),
                'child' => $child,
                'thisinvoice' => $retinvoice,
                'usercreate' => $user,
            ]);

            $this->logger->info('Notification sent.', [ 'email' => $administrator->user_email ]);
        }

        $this->flash->addMessage('success', 'Invoice sent to administrator, waiting for validation');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('invoice_list', ['child_id' => $retinvoice->idC]));

    })->setName('invoice_send_admin');

    /***************************************************************************
	 * GET '/invoices/weekly/'
	 *
	 * View summary page where user can select date to display specific weekly
	 *  Invoice
	 **************************************************************************/
	$this->get( '/weekly/all/{year}', function ( $req, $res, $args ) use ( $app ) {
        $School = new School($this);
        $Plan = new Plan($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Weekly Invoices';
        $user = $req->getAttribute('user');
        $view['id_user'] = $user->user_id;

        $idUser = $user->user_id;

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights '.$req->getAttribute('user_id').'.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        $admin = $School->checkIfAdmin($idUser);


        if($admin[0]->role == '1'){
            $school_weekly_plans = PlanHelper::assembleWeeklyInvoiceStats($Plan->getAllWeeklyInvoicesAllForAYear($_SESSION['school_id'], $args['year']));
        }elseif($user->user_type == "T") {
            $school_weekly_plans = PlanHelper::assembleWeeklyInvoiceStats($Plan->getAllWeeklyInvoicesTeacherForAYear($_SESSION['school_id'], $args['year'], $idUser));
        }else {
            $approved = 'yes';
            $school_weekly_plans = PlanHelper::assembleWeeklyInvoiceStats($Plan->getAllWeeklyInvoicesParentForAYear($_SESSION['school_id'], $args['year'], $idUser, $approved));
        }

        $view['admin'] = $admin[0];

		for ($i = 1; $i <= 52; $i++) {
			// Merge created plans into the empty week array
			if(array_key_exists($i,$school_weekly_plans)) {
				$view['weeks'][$i] = $school_weekly_plans[$i];
			}

			// getStartAndEndDate is declared in records.php
			$startEndDates = getStartAndEndDate($i, $args['year']);

			// Convert to UK date format
			$view['weeks'][$i]['start_date'] = date('M d', strtotime($startEndDates[0]));
			$view['weeks'][$i]['end_date'] = date('M d', strtotime($startEndDates[1]));
		}

		$view['year'] = $args['year'];

		return $this->view->render( $res, 'weeklyYearInvoice.html', $view );
	})->setName( 'weeklyYearInvoice' );

    /***************************************************************************
	 * GET '/invoice/weekly/{week}/{year}'
	 *
	 * View plans associated to a specific week of the year
	 **************************************************************************/
	$this->get( '/weekly/{week}/{year}', function ( $req, $res, $args ) use ( $app ) {
		$Plan = new Plan($this);
        $School = new School($this);
        $Child = new Child($this);
        $user = $req->getAttribute('user');

        $status = $_GET['status'];
        $approved = $_GET['approved'];
        $child_id = $_GET['child'];
        $validate = $_GET['validate'];


        $idUser = $user->user_id;

        $admin = $School->checkIfAdmin($idUser);
        $child = $Child->getAll($_SESSION['school_id']);


		$view['flash'] = $this->flash->getMessages();
		$view['title'] = 'Weekly Invoices';
        $view['admin'] = $admin[0];

		$startEndDates = getStartAndEndDate($args['week'], $args['year']);
		$view['year'] = $args['year'];
		$view['week'] = $args['week'];
		$view['start_date'] = date('M d', strtotime($startEndDates[0]));
		$view['end_date'] = date('M d', strtotime($startEndDates[1]));
        $view['child'] = $child;

        if($admin[0]->role == '1'){
            $view['plans'] = $Plan->getAllWeeklyInvoicesAllForAWeek($_SESSION['school_id'], $args['year'], $args['week'], $child_id, $status, $approved, $validate);
            $view['child_id'] = $child_id;
            $view['status'] = $status;
            $view['approved'] = $approved;
            $view['validate'] = $validate;

        }elseif($user->user_type == "T") {
            $view['plans'] = $Plan->getAllWeeklyInvoicesTeacherForAWeek($_SESSION['school_id'], $args['year'], $args['week'], $idUser);
        }else{
            $approved = 'yes';
            $view['plans'] = $Plan->getAllWeeklyInvoicesParentForAWeek($_SESSION['school_id'], $args['year'], $args['week'], $idUser, $approved);

        }
        
		return $this->view->render( $res, 'weeklyPlanInvoice.html', $view );
	})->setName( 'weeklyPlanInvoice' );

   
});
