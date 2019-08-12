<?php

$app->group('/checklists', function() use($app) {
    /***************************************************************************
     * GET 'checklists/:child_id/create'
     *
     * View observation checklist
     **************************************************************************/
    $this->get('/{child_id}/create', function($req, $res, $args) use($app) {
        $Checklist = new Checklist($this);
        $Child = new Child($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Add Milestone Observations';

        $view['child'] = $Child->getOne($args['child_id']);

        if ($view['child']->school_id != $_SESSION['school_id']) {
            $this->logger->notice('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['months'] = StringHandler::convertBirthdayToMonthCount($view['child']->child_birthday);
        $view['checklists'] = $Checklist->getAll($args['child_id'], $view['months']);

        if (isset($_GET['checklist'])) {
            $checklist_id = $_GET['checklist'];
        } else {
            $checklist_id = $view['checklists'][0]->checklist_id;
        }

        $view['current_checklist_id'] = $checklist_id;

        foreach ($Checklist->getCategories() as $category) {
            $view['categories'][$category->category_name] = $Checklist->getAllMilestones($checklist_id, $category->category_id);
        }

        /**
         * Go through all existing observations for the child and remember which
         * ones have already been marked as completed.
         */
        foreach ($Checklist->getObservations($args['child_id']) as $observation) {
            if ($observation->observation == '1') {
                $view['observed'][$observation->milestone_id] = true;
            }
        }

        $view['red_flags'] = $Checklist->getRedFlags($view['months']);

        return $this->view->render($res, 'checklistCreate.html', $view);
    })->setName('checklistCreate');

    /***************************************************************************
     * POST 'checklists/:checklist_id/create'
     *
     * Validate observation checklist form
     **************************************************************************/
    $this->post('/{child_id}/create', function($req, $res, $args) use($app) {
        $Checklist = new Checklist($this);
        $Child = new Child($this);
        $Timeline = new Timeline($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!count($data['milestones'])) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('checklistCreate', ['child_id' => $args['child_id']]));
        }

        $token_id = StringHandler::getToken(16);

        foreach($data['milestones'] as $k => $v) {
            if ($v === '1' || $v === '0') {
                $Checklist->createObservation($token_id, $k, $args['child_id'], $req->getAttribute('user_id'), $v, 0);
            }
        }

        foreach($data['red_flags'] as $v) {
            if ($v) {
                $Checklist->createObservation($token_id, $v, $args['child_id'], $req->getAttribute('user_id'), 1, 1);
            }
        }

        // Changed the public attribute to false, since we no longer want to show checklists to parents
        $Timeline->create($req->getAttribute('user_id'), $args['child_id'], 'checklist', $args['child_id'], 0, $data['comment']);

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('checklist', ['child_id' => $args['child_id']]));
    });
});
