<?php

$app->group('/infectionControl', function() use($app) {
    
    $this->get('/infectionLetters', function($req, $res, $args) use($app) {
        $Infection = new Infection($this);
        $data = $req->getParsedBody();
        
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Infection Control: Letters ';
        $view['samples'] = $Infection->getAllSample();
        $view['edit_l'] = $Infection->getEditLater(intval($req->getAttribute('user_id')));
        
        return $this->view->render($res, 'infectionLetters.html', $view);
    })->setName('infectionLetters');
    
    $this->get('/infectionLettersCreate', function($req, $res, $args) use($app) {
        $Infection = new Infection($this);
        $view['title'] = 'Infection Control: Letters';
        
        if( isset($_GET['edit_sample']) || isset($_GET['edit_letter'])){
            if(!empty($_GET['letter_sample'])){
                
                $view['body_l'] = $Infection->getOneSample($_GET['letter_sample']);
                return $this->view->render($res, 'infectionLettersCreate.html', $view);
                
            } elseif(!empty($_GET['letter_edit'])){
                
                $view['body_l'] = $Infection->getOne($_GET['letter_edit']);
                return $this->view->render($res, 'infectionLettersCreate.html', $view);
                
            } else{
                return $this->view->render($res, 'infectionLettersCreate.html', $view);
            }
        } else{
             return $this->view->render($res, 'infectionLettersCreate.html', $view);
        }
        
    })->setName('infectionLettersCreate');
    
    $this->post('/infectionLettersCreate', function($req, $res, $args) use($app) {
    
    /***************************************************************************
     * POST 'infectionLetters'
     *
     * Validate create letter form and redirect
     **************************************************************************/
        $Infection = new Infection($this);
        $School = new School($this);
        $Child = new Child($this);
        
        $data = $req->getParsedBody();
                
        $view['title'] = 'Infection Control: Letters ';
        
        $view['flash'] = $this->flash->getMessages();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');
        }
        
        if (!$data['sendto'] || !$data['description']) {
            $this->logger->info('User submitted incomplete form.');
            $this->flash->addMessage('danger', 'The form was filled out incompletely.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('infectionLettersCreate'));
        }
        
        if($data['edit_s']){
            $letter_id = $Infection->edit($data);
        } else{
            $letter_id = $Infection->create($req->getAttribute('user_id'), $_SESSION['school_id'], $data);
        }
        
        if (!$letter_id) {
            $this->logger->error('Infection::create failed.', [ 'user_id' => $req->getAttribute('user_id') ]);
            $this->flash->addMessage('danger', 'The letter could not be created.');
        }
        
        if ($data['sendto'] == 'T') {
            $users = $School->getActiveUsers($_SESSION['school_id']);
        } elseif ($data['sendto'] == 'P') {
            $users = $Child->getAllParentsForSchool($_SESSION['school_id']);
        } elseif ($data['sendto'] == 'SE') {
            $this->flash->addMessage('success', 'The letter was save.');
        } else {
            $this->logger->info('User provided an invalid sendto.', ['user_id' => $req->getAttribute('user_id'), 'sendto' => $data['sendto']]);
            $this->flash->addMessage('danger', 'You provided an invalid group of recipients.');

             return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('infectionLettersCreate'));
        }
        
        $message=explode("\n",$data['description']);
        
        if($users){
            foreach ($users as $user) {
                $this->mailer->send('infectionLetterSend.html', [
                    'to' => $user->user_email,
                    'subject' => 'Infection reported in preschool',
                    'first_name' => $user->user_first_name,
                    'message' => $message,
                    'school' => $School->getOne($_SESSION['school_id']),
                ]);
                
                $this->flash->addMessage('success', 'The letter was send.');
            }
            
        }        
        
        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('infectionLetters'));
        
    });
    
    $this->get('/audit/{question_sort}', function($req, $res, $args) use($app) {
        $Infection = new Infection($this);
        $School = new School($this);
         
        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Infection Control: Audit Infectious Disease';
        $view['audit_count'] = $Infection->auditCount();
        $view['question'] = $Infection->getQuestion($args['question_sort']);

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
        
            $view['progress'] = round((($view['question']->question_sort - 1) / $view['audit_count']) * 100);
        
        return $this->view->render($res, 'infectionAudit.html', $view);
        
    })->setName('audit');
    
    
     $this->post('/audit/{question_sort}/save', function($req, $res, $args) use($app) {
         $Infection = new Infection($this);
         $School = new School($this);
         $data = $req->getParsedBody();
         $category="Audit Infectious Disease";
         
         if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->notice('School::getUser invalid.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');
        } 
         $next_question_sort = $args['question_sort']+1;
         
         $question_id=$Infection->getQuestionId($args['question_sort']);
    
         $Infection->purge($_SESSION['school_id'], $args['question_sort'],$req->getAttribute('user_id'));
         
         //$Infection->createAnswer($_SESSION['school_id'], $args['question_sort'],$req->getAttribute('user_id'));
         
         $redirect = $this->router->pathFor('audit', ['question_sort' => $next_question_sort]);
         
         return $res->withStatus(302)->withHeader('Location', $redirect);
        
    })->setName('auditSave');
    
    //---------------------------------------------------------
    $this->post('zsd', function($req, $res, $args) use($app) {
        $Compliance = new Compliance($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        $Compliance->purge($_SESSION['school_id'], $args['question_id']);

        foreach ($data['answers'] as $k => $v) {
            if ($data['uploads'][$k]) {
                $file = $this->uploader->getFile($data['uploads'][$k]);
                $url = $file->getUrl();
            }

            $Compliance->create($_SESSION['school_id'], $req->getAttribute('user_id'), $args['question_id'], $v, $url);
        }

        $question = $Compliance->getQuestion($args['question_id']);
        $last_question = $Compliance->getLastQuestion();
        $school = $School->getOne($_SESSION['school_id']);

        /**
         * If user reached the last question, a different button appears in the
         * template, and this redirect should happen to summary.
         */
        if ($last_question->question_id == $args['question_id']) {
            $redirect = $this->router->pathFor('complianceSummary');
        } else {
            /**
             * Initial logical attempt to get the next question by the sort ID.
             */
            $i = $question->category_id;
            $j = $question->question_sort + 1;

            /**
             * This algorithm may appear tricky but it's actually straightforward.
             * We have a set of questions organized into categories and sort order,
             * so all we are going to do is order by these two criteria in the model
             * and iterate through the sort ID. If no question is found, we will
             * iterate through the categories with sort ID 1.
             *
             * At the same time we have questions that are only applicable to
             * certain school types. Once we found the next question, we will first
             * check if the question was delimited by a category, and either show
             * the question or or iterate to the next question.
             */
            do {
                $next_question = $Compliance->getQuestionByCategoryId($i, $j);

                if ($next_question) {
                    /**
                     * If this question applies to certain school types, it must be shown.
                     */
                    if ($next_question->school_categories) {
                        if (in_array($school->category_id, explode(',', $next_question->school_categories))) {
                            $next_question_id = $next_question->question_id;
                        } else {
                            /**
                             * Skip this question and loop to the next available one.
                             */
                            $j++;
                        }
                    } else {
                        $next_question_id = $next_question->question_id;
                    }
                } else {
                    /**
                     * Jump to the next category and pick the first question.
                     */
                    $i++;
                    $j = 1;
                }
            } while (!$next_question_id);

            $redirect = $this->router->pathFor('complianceDetails', ['question_id' => $next_question_id]);
        }

        return $res->withStatus(302)->withHeader('Location', $redirect);
    });
    
});

//------------------------------------------------------------------------------
