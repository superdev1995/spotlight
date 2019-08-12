<?php

$app->group('/billing', function() use($app) {
    /***************************************************************************
     * GET 'billing'
     *
     * View billing options and manage subscription
     **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Billing';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        $view['school'] = $School->getOne($_SESSION['school_id']);

        if ($view['school']->stripe_id) {
            $account = \Stripe\Customer::retrieve($view['school']->stripe_id);

            $subscriptions = $account->subscriptions;

            if (count($subscriptions['data'])) {
                foreach ($subscriptions['data'] as $v) {
                    $subscription = $account->subscriptions->retrieve($v['id']);

                    $view['subscriptions'][$v['id']] = [
                        'name' => $subscription->plan->name,
                        'start' => date('Y-m-d', $subscription->current_period_start),
                        'end' => date('Y-m-d',$subscription->current_period_end),
                        'cancelled' => $subscription->cancel_at_period_end,
                    ];
                }
            }
        }

        $year = date('Y');
        $view['month']=date('n');

        for ($i = $year; $i <= $year + 15; $i++) {
            $view['years'][] = $i;
        }

        $children = $Child->getAll($_SESSION['school_id']);

        $view['child_count'] = count($children);
        $view['amount'] = number_format(round(count($children) * $this->get('settings')['feePerChild'], 2), 2);
        $view['administrators'] = $School->getAdministrators($_SESSION['school_id']);
        $view['subscription_status'] = $School->getSubscriptionStatus($_SESSION['school_id']);

        return $this->view->render($res, 'billing.html', $view);
    })->setName('billing');

    /***************************************************************************
     * POST 'billing'
     *
     * Allow users to quick-add children before subscribing
     **************************************************************************/
    $this->post('', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $Room = new Room($this);
        $School = new School($this);
        $Timeline = new Timeline($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }

        /**
         * Since the form merely asks for a child's name, we will simply put all
         * of these new children in the first room available for the school.
         */
        $room = $Room->getFirst($_SESSION['school_id']);

        foreach ($data['name'] as $name) {
            if (!$name) {
                continue;
            }

            $child_id = $Child->createQuick($_SESSION['school_id'], $room->room_id, $name);

            if ($child_id) {
                $Timeline->create($req->getAttribute('user_id'), $child_id, 'create', $child_id, 1);
            }

            $this->logger->info('Child created on billing page.', [ 'child_id' => $child_id ]);
        }

        $this->flash->addMessage('success', 'Child profiles have been created.');

        return $res->withStatus(302)->withRedirect($this->router->pathFor('billing'));
    });

    /***************************************************************************
     * GET 'billing/expired'
     *
     * Advertise what features are available
     **************************************************************************/
    $this->get('/expired', function($req, $res, $args) use($app) {
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Subscription Expired';

        return $this->view->render($res, 'billingExpired.html', $view);
    })->setName('billingExpired');

    /***************************************************************************
     * GET 'billing/edit'
     *
     * Check VAT ID using an external API: vatlayer.com
     **************************************************************************/
    $this->post('/edit', function($req, $res, $args) use($app) {
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if ($data['vat_id']) {
            $this->logger->debug('VAT ID provided.', [ 'school_id' => $_SESSION['school_id'], 'vat_id' => $data['vat_id'] ]);

            /**
             * Only check the validity if the ID has changed, otherwise we will
             * be pinging the service at each update even though it is unlikely
             * that the VAT ID even changes often.
             */
            if ($data['vat_id'] != $school->school_vat_id) {
                $access_key = '45da3b8587440a36973789a6f5ddb3ad';

                $vat_number = $data['vat_id'];

                $ch = curl_init('http://apilayer.net/api/validate?access_key='.$access_key.'&vat_number='.$data['vat_id'].'');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                $json = curl_exec($ch);
                curl_close($ch);

                $validationResult = json_decode($json, true);

                $validationResult['valid'];
                $validationResult['query'];
                $validationResult['company_name'];
                $validationResult['company_address'];

                if ($validationResult['valid'] == true) {
                    $School->setVatId($_SESSION['school_id'], $data['vat_id']);

                    $this->logger->info('VAT ID updated.', [ 'school_id' => $_SESSION['school_id'], 'vat_id' => $data['vat_id'] ]);
                } else {
                    $this->logger->info('Invalid VAT ID.', [ 'school_id' => $_SESSION['school_id'], 'vat_id' => $data['vat_id'] ]);
                    $this->flash->addMessage('danger', 'The VAT ID you entered appears to be invalid.');

                    return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
                }
            }
        }

        $this->logger->info('Billing saved.', ['user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Billing details have been saved.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
    })->setName('billingEdit');

    /***************************************************************************
     * POST 'billing/subscribe'
     *
     * Create a new subscription with Stripe
     **************************************************************************/
    $this->post('/subscribe', function($req, $res, $args) use($app) {
        $Child = new Child($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->info('School::getUser failed.', [ 'school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $children = $Child->getAll($_SESSION['school_id']);

        if (count($children) < 1) {
            $this->logger->info('Too few children.', [ 'school_id' => $_SESSION['school_id'], 'child_count' => count($children) ]);
            $this->flash->addMessage('danger', 'Please add at least 1 child to your school before subscribing.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
        }

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        $school = $School->getOne($_SESSION['school_id']);

        /**
         * Check whether a subscription has already been created with Stripe
         * before (indicated by an existing Stripe ID).
         */
        if ($school->stripe_id) {
            /**
             * Attempt to retrieve the Stripe account using the Stripe ID
             */
            try {
                $account = \Stripe\Customer::retrieve($school->stripe_id);

                $this->logger->debug('Stripe customer retrieved.', [ 'stripe_id' => $account->id ]);
            } catch(Exception $e) {
                $this->logger->error('Could not update Stripe customer.', [ 'stripe_id' => $account->id ]);
                $this->flash->addMessage('danger', 'Could not update your details.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
            }
        } else {
            /**
             * Attempt to create a new Stripe account, since no Stripe ID exists
             */
            try {
                $account = \Stripe\Customer::create();

                $School->setStripeId($school->school_id, $account->id);

                $this->logger->debug('Stripe customer created.', [ 'stripe_id' => $account->id ]);
            } catch(Exception $e) {
                $this->logger->error('Could not create Stripe customer.', [ 'school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id') ]);
                $this->flash->addMessage('danger', 'Could not instantiate your billing details.');

                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
            }
        }

        if ($data['stripeToken']) {
            try {
                $account->source = $data['stripeToken'];
                $account->save();

                $this->logger->info('Customer card updated.', [ 'stripe_id' => $account->id ]);
            } catch(\Stripe\Error\Card $e) {
                $this->logger->error($e);
            }
        }

        /**
         * TeachKloud bills customers using metered billing each month for the
         * number of active children in their school (not counting deleted
         * profiles). Here we calculate their initial total. Stripe will then
         * send a monthly webhook to trigger a new invoice.
         */
        $children = $Child->getAll($_SESSION['school_id']);

        $amount = round($this->get('settings')['feePerChild'] * 100);
        $quantity = count($children);

        if ($school->school_vat_id) {
            $tax_percent = 0;

            $this->logger->info('VAT omitted from invoice.', [ 'school_id' => $_SESSION['school_id'] ]);
        } else {
            $tax_percent = 23;

            $this->logger->info('VAT added to invoice.', [ 'school_id' => $_SESSION['school_id'] ]);
        }

        $subscription = [
            'customer' => $account->id,
            'items' => array(
                array(
                    'plan' => 'monthly_1',
                    'quantity' => $quantity,
                ),
            ),
            'tax_percent' => $tax_percent,
        ];
        
        if ($data['coupon']) {
            $subscription['coupon'] = $data['coupon'];
        }
        
        try {
            \Stripe\Subscription::create($subscription);
        } catch (\Exception $e) {
            $this->logger->error($e);
            $this->flash->addMessage('danger', $e->getMessage());
            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
        }
    
        foreach ($School->getAdministrators($_SESSION['school_id']) as $administrator) {
            ListHandler::setSubscribed($administrator->user_email, 'Yes', $req->getAttribute('user')->user_type);
        }

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
    })->setName('billingSubscribe');

    /***************************************************************************
     * POST 'billing/unsubscribe'
     *
     * Cancel a Stripe subscription
     **************************************************************************/
    $this->post('/unsubscribe', function($req, $res, $args) use($app) {
        $School = new School($this);

        $data = $req->getParsedBody();

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        $school = $School->getOne($_SESSION['school_id']);

        try {
            $account = \Stripe\Customer::retrieve($school->stripe_id);

            $subscription = $account->subscriptions->retrieve($data['subscription_id']);

            \Stripe\Subscription::update(
                $subscription->id,
                ['cancel_at_period_end' => true]
            );

            $this->logger->info('Subscription cancelled.', [ 'subscription_id' => $data['subscription_id'] ]);
            $this->flash->addMessage('success', 'You subscription was put on hold.');
        } catch (Exception $e) {
            $this->logger->error('Could not cancel subscription.', [ 'subscription_id' => $data['subscription_id'] ]);
            $this->flash->addMessage('warning', 'You subscription was not put on hold.');
        }

        foreach ($School->getAdministrators($_SESSION['school_id']) as $administrator) {
            ListHandler::setSubscribed($administrator->user_email, 'No', $req->getAttribute('user')->user_type);
        }

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('billing'));
    })->setName('billingUnsubscribe');

    /***************************************************************************
     * POST 'billing/resubscribe'
     *
     * Resubscribe to an active subscription after cancellation
     **************************************************************************/
    $this->post('/resubscribe', function($req, $res, $args) use($app) {
        $School = new School($this);

        $data = $req->getParsedBody();

        \Stripe\Stripe::setApiKey($this->get('settings')['stripe']['secretKey']);

        $school = $School->getOne($_SESSION['school_id']);

        try {
            $subscription = \Stripe\Subscription::retrieve($data['subscription_id']);

            $item_id = $subscription->items->data[0]->id;

            /**
             * We must do this check because we cannot simply provide the
             * coupon in the query in case it is empty. Stripe would return an
             * error.
             */
            if ($data['coupon']) {
                \Stripe\Subscription::update($data['subscription_id'], [
                    'cancel_at_period_end' => false,
                    'items' => [
                        [
                            'id' => $item_id,
                            'plan' => 'monthly_1',
                        ],
                    ],
                    'coupon' => $data['coupon'],
                ]);
            } else {
                \Stripe\Subscription::update($data['subscription_id'], [
                    'cancel_at_period_end' => false,
                    'items' => [
                        [
                            'id' => $item_id,
                            'plan' => 'monthly_1',
                        ],
                    ],
                ]);
            }

            $this->logger->info('Subscription resumed.', [ 'subscription_id' => $data['subscription_id'] ]);
            $this->flash->addMessage('success', 'You subscription has been resumed.');
        } catch (Exception $e) {
            $this->logger->error('Could not resubscribe subscription.', [ 'subscription_id' => $data['subscription_id'] ]);
        }
                    $this->flash->addMessage('success', 'You subscription has been resumed.');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('billing'));
    })->setName('billingResubscribe');
});

