<?php

$app->group('/fees', function() use($app) {
    /***************************************************************************
     * GET 'fees'
     *
     * View billing form
     **************************************************************************/
    $this->get('/[{child_id}]', function($req, $res, $args) use($app) {
        $view['title'] = 'Billing';
        $Child = new Child($this);
        $fee = new Fee($this);
        $view['child'] = $Child->getOne($args['child_id']);
        $view['date'] = $fee->day();
        if (isset($_COOKIE['auth_token'])) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        $view['previous_data']=$fee->selectBillingOne($token->user_id,$args['child_id'] ,$view['date']);
        $view['previous']=$fee->selectBillingPrevious($token->user_id,$args['child_id'] ,$view['date']);
        return $this->view->render($res, 'fees.html', $view);
    })->setName('fees');
    /***************************************************************************
     * POST 'fees'
     *
     * Save the informacion of the billing
     **************************************************************************/
    $this->post('/[{child_id}]', function($req, $res, $args) use($app) {
        $data=$req->getParsedBody();
        $fee= new Fee($this);
        $view['title'] = 'Billing';
        $Child = new Child($this);
        $view['child'] = $Child->getOne($args['child_id']);
        $view['date'] = $fee->day();
        if (isset(($_COOKIE['auth_token']))) {
            $Auth = new Auth($this);
            $token = $Auth->validateToken($_COOKIE['auth_token']);
            $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);
        }
        $cont=1;
        do{
            if (isset($data["type$cont"])){  
                $fee->create($data['id_user'],$data['business_name'],$data['business_city'],$data['business_PC'],$data['business_phone'],$data['business_email'],$data['id_child'],$data['client'],$data['client_address'],$data['client_city'],$data['client_PC'],$data['invoice_number'],$data['date'],$data['reg_no'],$data['currency'],$data['fee'.$cont],$data['hours'.$cont],$data['ecce'.$cont],$data['tec'.$cont],$data['acsz'.$cont],$data['extras'.$cont],$data['discount'.$cont],$data['type'.$cont],$data['total']);
            }
            $cont++;
        }while(count($data)>$cont);
        if ($data['avatar']) {
            $avatar = $this->uploader->getFile($data['avatar']);

            if ($avatar->data['is_image']) {
                if ($fee->selectBillingOne($data['id_user'],$data['id_child'],$data['date'])->url_logo) {
                    $this->logger->debug('Attempt to delete old avatar.', [ 'url' => $fee->selectBillingOne($data['id_user'],$data['id_child'],$data['date'])->url_logo ]);

                    /**
                     * Delete the existing avatar if there is any found in the
                     * user_avatar_url column to save disk space.
                     */
                    $old_avatar = $this->uploader->getFile($fee->selectBillingOne($data['id_user'],$data['id_child'],$data['date'])->url_logo);
                    $old_avatar->delete();
                }

                /**
                 * Save the resized avatar only. We never know how big the
                 * original file size is and clutter the CDN disk space.
                 */
                $resized_avatar = $this->uploader->createLocalCopy($avatar->getUrl());
                $resized_avatar->store();

                $avatar->delete();

                $fee->setAvatar($data['id_user'],$avatar->getUrl());
            }
        }
        $parents=$fee->parentEmail($data['id_child']);
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
        $view['previous_data']=$fee->selectBillingOne($token->user_id,$args['child_id'] ,$view['date']);
        $view['previous']=$fee->selectBillingPrevious($token->user_id,$args['child_id'] ,$view['date']);
        return $this->view->render($res, 'fees.html', $view);
    });
});
