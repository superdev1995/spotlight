<?php

require_once('./../vendor/autoload.php');

$app->group('/profile', function () use ($app) {
    /***************************************************************************
     * GET 'profile'
     *
     * See profile of the school
     **************************************************************************/
    $this->get('', function ($req, $res, $args) use ($app) {
        $Country = new Country($this);
        $School = new School($this);
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Pre-School Profile';

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        // \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);
        // $school = $School->getOne($_SESSION['school_id']);

        // $balance = \Stripe\Balance::retrieve(
        //     ["stripe_account" => $school->stripe_connect_id]
        // );


        //$charge = \Stripe\Charge::all(["limit" => 3]);
        // $charge = \Stripe\Charge::all([], [
        //     'api_key' => $this->get('settings')['stripe']['secretKey'],
        //     'stripe_account' => $school->stripe_connect_id
        // ]);

        // $charge1 = json_decode($charge->__toJSON(), true);
        //var_dump($charge1);

        // $i = 0;
        // foreach($charge1 as $charge2=> $it){
        //     //print_r($charge1['data'][$i]);
        //     $i++;
        //     

        // }

        /*foreach($charge['data'] as $charge) {
            //echo $charge['id'];

            $chargeById = \Stripe\Charge::retrieve(
                $charge['id'],
                ["stripe_account" => $school->stripe_connect_id]);

            $charge1 = $chargeById->__toArray(false);
            echo($charge1);
            ?><br><br><?php

            $view['chargeById'] = $charge1;
        }*/



        //$test = json_decode($charge->__toJSON(), true);
        //$test = $charge->__toArray(false);
        //$test = $charge->__toArray(true);
        //$rest = json_decode($charge);
        //print_r($test['data']);

        //var_dump($test);
        //var_dump($test);

        //for($i=0;$i<5;$i++){
        //var_dump($test['data']);

        /*foreach ($test as $dataaa){
            foreach($dataaa['data'] as $bien => $data){
               // echo $data;
            }

        }*/
        //}


        // $view['balance'] = $balance;
        // $view['charge'] = $charge1;
        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['schoolUS'] = $School->getOneUS($_SESSION['school_id']);
        $view['categories'] = $School->getCategories();
        $view['countries'] = $Country->getCountries();

        return $this->view->render($res, 'school.html', $view);
    })->setName('school');

    /***************************************************************************
     * GET 'profile/edit'
     *
     * See edit form of the school
     **************************************************************************/
    $this->get('/edit', function ($req, $res, $args) use ($app) {
        $Country = new Country($this);
        $School = new School($this);
        $getadmin = $School->getAdminAll($_SESSION['school_id']);
        $school = $School->getOne($_SESSION['school_id']);
        //$account = \Stripe\Account::retrieve($school->stripe_connect_id);
        $admin = $getadmin[0];

        $view['admin'] = $admin;
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Pre-School Profile';

        $view['stripeCode'] = $_GET['code'];
        $view['stripeScope'] = $_GET['scope'];

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_item = $School->getOne($_SESSION['school_id']);

        $view['uploaderScriptTag'] = $this->uploader->widget->getScriptTag();
        $view['categories'] = $School->getCategories($school_item->country_id);
        $view['school'] = $school_item;
        $view['schoolUS'] = $School->getOneUS($_SESSION['school_id']);

        $view['countries'] = $Country->getCountries();

        return $this->view->render($res, 'schoolEdit.html', $view);
    })->setName('schoolEdit');

    /***************************************************************************
     * POST 'profile/edit'
     *
     * Save profile of the school
     **************************************************************************/
    $this->post('/edit', function ($req, $res, $args) use ($app) {
        $School = new School($this);
        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Insufficient rights.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id'), 'role' => $school_user->role]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('schoolEdit'));
        }

        if (!$data['name'] || !$data['category_id'] || !$data['street'] || !$data['city'] || !$data['postal_code'] || !$data['phone']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('schoolEdit'));
        }

        if (!$School->setDetails($_SESSION['school_id'], $data)) {
            $this->logger->error('School::setDetails failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'School details could not be saved.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('schoolEdit'));
        }

        if ($data['avatar']) {
            $school = $School->getOne($_SESSION['school_id']);

            $this->logger->debug('Attempting upload.', ['avatar' => $data['avatar']]);

            $avatar = $this->uploader->getFile($data['avatar']);

            if ($avatar->data['is_image']) {
                if ($school->school_avatar_url) {
                    $this->logger->debug('Attempt to delete old avatar.', ['url' => $school->school_avatar_url]);

                    $old_avatar = $this->uploader->getFile($school->school_avatar_url);
                    $old_avatar->delete();
                }

                $resized_avatar = $this->uploader->createLocalCopy($avatar->getUrl());
                $resized_avatar->store();

                $avatar->delete();

                $School->setAvatar($_SESSION['school_id'], $resized_avatar->getUrl());
            }
        }

        $this->logger->info('School updated.', ['school_id' => $school_id, 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Your school <strong>' . $data['name'] . '</strong> has been updated.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));
    });


    /***************************************************************************
     * GET 'profile/stipeAdd'
     *
     * See form view for add stipe acompte
     **************************************************************************/
    $this->get('/stripeAdd', function ($req, $res, $args) use ($app) {
        $Country = new Country($this);
        $School = new School($this);
        $getadmin = $School->getAdminAll($_SESSION['school_id']);
        $school = $School->getOne($_SESSION['school_id']);
        //$account = \Stripe\Account::retrieve($school->stripe_connect_id);
        $admin = $getadmin[0];

        $view['admin'] = $admin;
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Add Stripe Account';

        $view['stripeCode'] = $_GET['code'];
        $view['stripeScope'] = $_GET['scope'];

        if ($school->stripe_connect_id) {
            $this->flash->addMessage('danger', 'You have already linked a Stripe account.');
            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_item = $School->getOne($_SESSION['school_id']);

        $view['uploaderScriptTag'] = $this->uploader->widget->getScriptTag();
        $view['categories'] = $School->getCategories($school_item->country_id);
        $view['school'] = $school_item;
        $view['schoolUS'] = $School->getOneUS($_SESSION['school_id']);
        $view['countries'] = $Country->getCountries();

        return $this->view->render($res, 'addStripe.html', $view);
    })->setName('stripeAdd');

    //acct_1DakvKDSfAhSnXLX
    //IE89370400440532013000
    /***************************************************************************
     * POST 'profile/stripeAdd'
     *
     * Save info strip Accompte of the school
     **************************************************************************/
    $this->post('/stripeAdd', function ($req, $res, $args) use ($app) {
        $School = new School($this);
        $school = $School->getOne($_SESSION['school_id']);
        $getadmin = $School->getAdminAll($_SESSION['school_id']);
        $data = $req->getParsedBody();

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        if (!$school->stripe_connect_id) {
          try {
            $result = \Stripe\Account::create(array(
                "type" => "custom",
                "country" => "IE",
                "email" => $data['email'],

                "legal_entity" => array(

                    "first_name" => $data['first_name'],
                    "last_name" => $data['last_name'],
                    'business_name' => $data['business_name'],
                    'business_tax_id' => '000000000',
                    // 'personal_id_number' => '000000000',

                        "dob" => array(
                            "day" => $data['dob_day'],
                            "month" => $data['dob_month'],
                            "year" => $data['dob_year']
                        ),

                        "address" => array(
                            "city" => $data['city'],
                            "line1" => $data['street'],
                            "postal_code" => $data['postal_code'],
                        ),

                    "type" => "individual",

                        "verification" => array(
                            "document" => null
                        )
                ),

                "external_account" => array(
                "object" => "bank_account",
                "country" => $data['country'],
                "currency" => "eur",
                "account_holder_name" => $data['account_name'],
                "account_holder_type" => 'individual',
                "account_number" => $data['account_number']

                ),
            ));

            $id_account = $result['id'];
            $School->setIdStripe($_SESSION['school_id'], $id_account);

            $stripeAccountId = $result->id;
            $stripeAccountObj = \Stripe\Account::retrieve($stripeAccountId);
            $stripeAccountObj->tos_acceptance->date = time();
            $stripeAccountObj->tos_acceptance->ip = $_SERVER['REMOTE_ADDR'];
            $stripeAccountObj->save();

            $this->flash->addMessage('success', 'You have successfully created a stripe account.');
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));

        } catch(\Stripe\Error\Card $e) {
            // Since it's a decline, \Stripe\Error\Card will be caught
            $body = $e->getJsonBody();
                $err  = $body['error'];
                // return $res->withJson($err);
                // $this->logger->error($err);
                foreach($err as $e){
                    return $res->withJson($e);
                    $this->logger->error( $e);
                }
                $this->flash->addMessage('danger', $err['message']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));

          } catch (\Stripe\Error\RateLimit $e) {
            // Too many requests made to the API too quickly
            $body = $e->getJsonBody();
                $err  = $body['error'];
                // return $res->withJson($err);
                // $this->logger->error($err);
                foreach($err as $e){
                    $this->logger->error( $e);
                }
                $this->flash->addMessage('danger', $err['message']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));

          } catch (\Stripe\Error\InvalidRequest $e) {
            // Invalid parameters were supplied to Stripe's API
            $body = $e->getJsonBody();
                $err  = $body['error'];
                // return $res->withJson($err);
                // $this->logger->error($err);
                foreach($err as $e){
                    $this->logger->error( $e);
                }
                $this->flash->addMessage('danger', $err['message']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));

          } catch (\Stripe\Error\Authentication $e) {
            // Authentication with Stripe's API failed
            $body = $e->getJsonBody();
                $err  = $body['error'];
                // return $res->withJson($err);
                foreach($err as $e){
                    $this->logger->error( $e);
                }
                
                $this->flash->addMessage('danger', $err['message']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));

          } catch (\Stripe\Error\ApiConnection $e) {
            // Network communication with Stripe failed
            $body = $e->getJsonBody();
                $err  = $body['error'];
                // $this->logger->error( $err);
                foreach($err as $e){
                    $this->logger->error( $e);
                }
                $this->flash->addMessage('danger', $err['message']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));

          } catch (\Stripe\Error\Base $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                // return $res->withJson($err);
                // $this->logger->error( $err);
                foreach($err as $e){
                    $this->logger->error( $e);
                }
                $this->flash->addMessage('danger', $err['message']);
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));

          } catch(Exeption $e) {
                $this->logger->error( $e);
                $this->flash->addMessage('danger', 'There was a problem creating the account.');
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));
          }

        } else {
             try {
                 $stripeAccountObj = \Stripe\Account::retrieve($school->stripe_connect_id);
                 $this->logger->debug('Stripe customer retrieved.', [ 'stripe_id' => $stripeAccountObj->id ]);
                 return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));

            } catch(Exception $e) {
                $this->logger->error('Could not update Stripe customer.');
                $this->flash->addMessage('danger', 'Could not update your details.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('stripeAdd'));
            }
        }
    });


    /***************************************************************************
     * GET 'profile/stripeEdit'
     *
     * 
     **************************************************************************/
    $this->get('/stripeEdit', function ($req, $res, $args) use ($app) {
        $Country = new Country($this);
        $School = new School($this);
        $getadmin = $School->getAdminAll($_SESSION['school_id']);
        $school = $School->getOne($_SESSION['school_id']);
        $admin = $getadmin[0];

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);
        $stripeAccountObj = \Stripe\Account::retrieve($school->stripe_connect_id);

        $view['stripe'] = $stripeAccountObj;
        $view['admin'] = $admin;
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Edit Stripe Account';

        $view['stripeCode'] = $_GET['code'];
        $view['stripeScope'] = $_GET['scope'];

        if ($school->stripe_connect_id == "") {
            $this->flash->addMessage('danger', 'You don\'t have a linked stripe account.');
            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_item = $School->getOne($_SESSION['school_id']);

        $view['uploaderScriptTag'] = $this->uploader->widget->getScriptTag();
        $view['categories'] = $School->getCategories($school_item->country_id);
        $view['school'] = $school_item;
        $view['schoolUS'] = $School->getOneUS($_SESSION['school_id']);
        $view['countries'] = $Country->getCountries();

        return $this->view->render($res, 'editStripe.html', $view);
    })->setName('stripeEdit');

    //acct_1DakvKDSfAhSnXLX
    //IE89370400440532013000
    /***************************************************************************
     * POST 'profile/stripeEdit'
     *
     * Save info strip Accompte of the school
     **************************************************************************/
    $this->post('/stripeEdit', function ($req, $res, $args) use ($app) {
        $School = new School($this);
        $school = $School->getOne($_SESSION['school_id']);
        $data = $req->getParsedBody();

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        if($school->stripe_connect_id) {
            try {
                $stripeAccountObj = \Stripe\Account::retrieve($school->stripe_connect_id);


                $stripeAccountObj->business_name = $data['business_name'];
                $stripeAccountObj->email = $data['email'];

                $stripeAccountObj->save();
                $this->logger->debug('Stripe customer retrieved.', ['stripe_id' => $stripeAccountObj->id]);

                $this->flash->addMessage('success', 'You have changed your stripe account.');
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));

            } catch (Exception $e) {
                $this->logger->error('Could not update Stripe customer.');
                $this->flash->addMessage('danger', 'Could not update your details account.');
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
            }
        }

    });

    $this->get('/stripeDelete', function ($req, $res, $args) use ($app) {})->setName("stripeDelete");
    /***************************************************************************
     * POST 'profile/stripeDelete'
     *
     * Save info strip Accompte of the school
     **************************************************************************/
    $this->post('/stripeDelete', function ($req, $res, $args) use ($app) {
        $School = new School($this);
        $school = $School->getOne($_SESSION['school_id']);

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        if($school->stripe_connect_id) {
            try {
                $stripeAccountObj = \Stripe\Account::retrieve($school->stripe_connect_id);

                $stripeAccountObj->delete();
                $id_account = "";
                $School->setIdStripe($_SESSION['school_id'], $id_account);

                //return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('profile'));
                $this->flash->addMessage('success', 'Stripe account is now delete.');
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));

            } catch (Exception $e) {
                echo $e;
                $this->logger->error('An error occurred while deleting the account.');
                $this->flash->addMessage('danger', 'Could not update your details.');

               return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));
            }
        }

         return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('school'));

    });

});
