<?php

$app->group('/policies', function() use($app) {
    /***************************************************************************
	 * GET '/policies'
	 *
	 * 
	 **************************************************************************/
    $this->get('', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Policies';
        $view['policies'] = $Policy->getAll($_SESSION['school_id']);

        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if(!$view['school_user']){
            $school_id = $School->getSchoolID( $req->getAttribute('user_id'), $_SESSION['child_id'] )->school_id;
            $view['school_user'] = $School->getParent( $req->getAttribute('user_id'));
            $view['policies'] = $Policy->getAllPoliciesForParent($school_id, $view['school_user']->user_id);
        }

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
        
        return $this->view->render($res, 'policy.html', $view);
    })->setName('policy');

    /***************************************************************************
	 * POST '/policies/share'
	 *
	 * 
	 **************************************************************************/
    $this->post('/share', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);
		$Child = new Child($this);

        $view['title'] = 'Policies';
        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $view['sharepols'] = $Policy->shareCheck($_SESSION['school_id']);

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $new_policies = false;
        foreach ($view['sharepols'] as $y){
            if (isset($_POST[$y->policy_id])){    
                if($y->policy_public == 0){
                    $new_policies = true;
                    $Policy->editPolicyParent($_SESSION['school_id'], $y->policy_id, 0);         
                    $Policy->share($y->policy_id, $_SESSION["school_id"]);
                }
            }
        }

        if(isset($_POST['email']) && $new_policies){
            foreach($Child->getAll($_SESSION['school_id']) as $child){
                foreach ($Child->getParents($child->child_id) as $parent) {
                    if($parent->user_notify_record) {             
                        $this->mailer->send('policiesNotify.html', [
                            'to' => $parent->user_email,
                            'subject' => 'New policies created',
                            'first_name' => $parent->user_first_name,
                            'school' => $School->getOne($_SESSION['school_id']),
                        ]);
                    }
                }
            }
            $this->logger->info("Emails sended", ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
        }

        $view['policies'] = $Policy->getAll($_SESSION['school_id']);
        $this->flash->addMessage('success', 'Your policies have been shared.');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('policy'));
    })->setName('sharePolicies');


    /***************************************************************************
	 * POST '/policies/unshare'
	 *
	 * 
	 **************************************************************************/
    $this->post('/unshare', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $view['title'] = 'Policies';
        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $view['sharepols'] = $Policy->shareCheck($_SESSION['school_id']);

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        foreach ($view['sharepols'] as $y){
            if (isset($_POST[$y->policy_id])){
                $Policy->unshare($y->policy_id, $_SESSION["school_id"]);
            }
        }

        $view['policies'] = $Policy->getAll($_SESSION['school_id']);
        $this->flash->addMessage('danger', 'Your policies have been unshared.');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('policy'));
    })->setName('unsharePolicies');

    /***************************************************************************
	 * POST '/policies/delete'
	 *
	 * 
	 **************************************************************************/
    $this->post('/delete', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $view['title'] = 'Policies';
        $view['school_user'] = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));
        $view['sharepols'] = $Policy->shareCheck($_SESSION['school_id']);

        if (!$view['school_user']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        foreach ($view['sharepols'] as $y){
            if (isset($_POST[$y->policy_id])){
                $Policy->purge($y->policy_id, $_SESSION["school_id"]);
            }
        }

        $view['policies'] = $Policy->getAll($_SESSION['school_id']);
        $this->flash->addMessage('danger', 'Your policies have been deleted.');
        return $res->withStatus(302)->withRedirect($this->router->pathFor('policy'));
    })->setName('deletePolicies');

    /***************************************************************************
	 * GET '/policies/policyNew/'
	 *
	 * 
	 **************************************************************************/
    $this->get('/policyNew', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Create Policy';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
		
		$policies = $Policy->getAll($_SESSION['school_id']);
        $x = 0;
        foreach( $policies as $policy){
            if($policy->policy_default != 0) $x++;
        }

        if($x >= 10) {
            $this->logger->info('You can\'t create another policy.');
            $this->flash->addMessage('danger', 'You are not allowed to create more policies.');
			return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policy'));
        }

        return $this->view->render($res, 'policyNew.html', $view);
    })->setName('policyNew');

    /***************************************************************************
	 * POST '/policies/policyNew/'
	 *
	 * 
	 **************************************************************************/
    $this->post('/policyNew', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
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

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policy'));
        }

        if (!$data['polName']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyNew'));
        }
		
        $policies = $Policy->getAll($_SESSION['school_id']);
        $x = 0;
        foreach( $policies as $policy){
            if($policy->policy_default != 0) $x++;
        }

        if($x < 10) {
            $Policy->createPolicy($data, $_SESSION['school_id']);
            $this->logger->info('Policy created.', ['policy_id' => $args['policy_id'], 'school_id' => $school_id, 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('success', 'Your policy has been created.');
        }
        else {
            $this->logger->info('You can\'t create another policy.');
            $this->flash->addMessage('danger', 'You are not allowed to create more policies.');
        }
		
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policy'));
    });

    /***************************************************************************
	 * GET '/policies/{policy_id}'
	 *
	 *
	 **************************************************************************/
    $this->get('/{policy_id}', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'View Policy';
        $view['policy'] = $Policy->getOne($args['policy_id'], $_SESSION['school_id']);

        if (!$view['policy']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if(!$school_user){
            $school_id = $School->getSchoolID( $req->getAttribute('user_id'), $_SESSION['child_id'] )->school_id;
            $school_user = $School->getParent( $req->getAttribute('user_id'));
            $view['policy'] = $Policy->getOne($args['policy_id'], $school_id);
            $Policy->editSinglePolicyParent($school_id, $args['policy_id'], $req->getAttribute('user_id'), 1);
        }
        else {
            $view['parents'] = $Policy->getPolicyParents($_SESSION['school_id'], $args['policy_id']);
        }

        if (!$school_user) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['school_user'] = $school_user;

        return $this->view->render($res, 'policyDetails.html', $view);
    })->setName('policyDetails');

    /***************************************************************************
	 * GET '/policies/{policy_id}/edit'
	 *
	 * 
	 **************************************************************************/
    $this->get('/{policy_id}/edit', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Edit Policy';
        $view['policy'] = $Policy->getOne($args['policy_id'], $_SESSION['school_id']);

        $policy = $Policy->getOne($args['policy_id'], $_SESSION['school_id']);

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Insufficient rights.', ['school_id' => $policy->school_id, 'role' => $school_user->role]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $policy->policy_id]));
        }

        if (!$view['policy']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        return $this->view->render($res, 'policyEdit.html', $view);
    })->setName('policyEdit');

    /***************************************************************************
	 * POST '/policies/{policy_id}/edit'
	 *
	 * 
	 **************************************************************************/
    $this->post('/{policy_id}/edit', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);
        $Child = new Child($this);

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

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $args['policy_id']]));
        }

        if (!$data['description'] && !$data['file']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $args['policy_id']]));
        }

        $policy = $Policy->getOne($args['policy_id'], $_SESSION['school_id']);
        if($policy->created_at == null || $policy->file_url != $data['file_url'] || $policy->body != $data['description']){
            if ($data['file']) {
                $file = $this->uploader->getFile($data['file']);
                $file->store();
    
                $this->logger->debug('Saving policy document.', [ 'url' => $file->getUrl() ]);
    
                $data['file_url'] = $file->getUrl();
            } else {
                $data['file_url'] = null;
            }
            
            if (!($policy->created_at != null ? 
                  $Policy->update($args['policy_id'], $_SESSION['school_id'], $data) : 
                  $Policy->create($args['policy_id'], $_SESSION['school_id'], $data))) {
                $this->logger->error('Policy::create failed.', ['policy_id' => $args['policy_id'], 'school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
                $this->flash->addMessage('danger', 'Policy could not be saved.');
    
                return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $args['policy_id']]));
            }

            if($policy->created_at == null){
                foreach($Child->getAll($_SESSION['school_id']) as $child){
                    foreach ($Child->getParents($child->child_id) as $parent) {
                        $Policy->createPolicyParent($_SESSION['school_id'], $args['policy_id'], $parent->user_id);
                    }
                }
            }
            else{
                $Policy->editPolicyParent($_SESSION['school_id'], $args['policy_id'], 0);
            }
        }
        

        $this->logger->info('Policy updated.', ['policy_id' => $args['policy_id'], 'school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'Your policy has been saved.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $args['policy_id']]));
    });

    /***************************************************************************
	 * POST '/policies/{policy_id}/announce'
	 *
	 * 
	 **************************************************************************/
    $this->post('/{policy_id}/announce', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);
        $Child = new Child($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $policy = $Policy->getOne($args['policy_id'], $_SESSION['school_id']);

        /**
         * We want to get the school_id saved in the policy itself to ensure
         * that we check for the user’s permission associated with it.
         */
        $school_user = $School->getUser($policy->school_id, $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Insufficient rights.', ['school_id' => $policy->school_id, 'role' => $school_user->role]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $policy->policy_id]));
        }

        foreach($School->getActiveUsers($policy->school_id) as $user) {
            if($user->user_id == $req->getAttribute('user_id'))
                continue;
            $this->mailer->send('policyAnnounce.html', [
                'to' => $user->user_email,
                'subject' => 'Policy updated: '.$policy->policy_name,
                'first_name' => $user->user_first_name,
                'message' => $data['message'],
                'policy' => $policy,
                'school' => $School->getOne($policy->school_id),
            ]);
            $this->logger->info("Emails to teachers sended", ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
        }

        if(isset($_POST['parents'])){
            foreach($Child->getAll($_SESSION['school_id']) as $child){
                foreach ($Child->getParents($child->child_id) as $parent) {
                    if (!$parent->user_notify_record) {
                        continue;
                    }
                    $this->mailer->send('policyAnnounce.html', [
                        'to' => $parent->user_email,
                        'subject' => 'Policy updated: '.$policy->policy_name,
                        'first_name' => $parent->user_first_name,
                        'message' => $data['messageParents'],
                        'policy' => $policy,
                        'school' => $School->getOne($policy->school_id),
                    ]);
                }
            }
            $this->logger->info("Emails to parents sended", ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
        }

        $this->logger->info('Policy update announced.', ['policy_id' => $args['policy_id'], 'school_id' => $policy->school_id]);
        $this->flash->addMessage('success', 'Your policy change has been announced to all teachers.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policyDetails', ['policy_id' => $policy->policy_id]));
    })->setName('policyAnnounce');

    /***************************************************************************
	 * POST '/policies/{policy_id}/delete'
	 *
	 * 
	 **************************************************************************/
    $this->post('/{policy_id}/delete', function($req, $res, $args) use($app) {
        $Policy = new Policy($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $policy = $Policy->getOne($args['policy_id'], $_SESSION['school_id']);
        
        /**
         * We want to get the school_id saved in the policy itself to ensure
         * that we check for the user’s permission associated with it.
         */
        $school_user = $School->getUser($policy->school_id, $req->getAttribute('user_id'));

        if ($school_user->role != 1) {
            $this->logger->notice('Insufficient rights.', ['school_id' => $policy->school_id, 'role' => $school_user->role]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policy'));
        }

        if (!$Policy->purge( $args['policy_id'],$policy->school_id)) {
            $this->logger->error('Policy::delete failed.', ['policy_id' => $args['policy_id'], 'school_id' => $policy->school_id]);
            $this->flash->addMessage('danger', 'Policy could not be deleted.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policy'));
        }

        $this->logger->info('Policy deleted.', ['policy_id' => $args['policy_id'], 'school_id' => $school_id]);
        $this->flash->addMessage('success', 'Your policy has been deleted.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('policy'));
    })->setName('policyDelete');
});