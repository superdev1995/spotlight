<?php

$app->group('/gdpr', function() use($app) {
    $this->get('', function($req, $res, $args) use($app) {
        $Gdpr = new Gdpr($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'GDPR Self-Audit';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['categories'] = $Gdpr->getCategories($view['school']->country_id);
        $view['first_question'] = $Gdpr->getFirstQuestion($view['school']->country_id);

        return $this->view->render($res, 'gdpr.html', $view);
    })->setName('gdpr');
    
    $this->get('/summary', function($req, $res, $args) use($app) {
        $Gdpr = new Gdpr($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'GDPR Self-Audit Summary';

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['categories'] = $Gdpr->getCategories($view['school']->country_id);

        $questions = $Gdpr->getQuestions();
        
        foreach($questions as $question) {
            $view['questions'][$question->question_id] = [
                "question" => $question,
                "answer" => $Gdpr->getQuestionAnswer($_SESSION['school_id'], $question->question_id),
            ];
            
            if($view['questions'][$question->question_id]['question']->question_multiple_choice){
                $selectedAnswer = $Gdpr->getAnswer($view['questions'][$question->question_id]['answer']->answer_id);
                $view['questions'][$question->question_id]['selected_answer'] = $selectedAnswer;
            }
        }
        
        $view['first_conducted'] = $Gdpr->getFirstConducted($_SESSION['school_id']);
        $view['last_conducted'] = $Gdpr->getLastConducted($_SESSION['school_id']);

        return $this->view->render($res, 'gdprSummary.html', $view);
    })->setName('gdprSummary');
    
    $this->get('/{question_id}', function($req, $res, $args) use($app) {
        $Gdpr = new Gdpr($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'GDPR Self-Audit';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['categories'] = $Gdpr->getCategories();
        $view['question'] = $Gdpr->getQuestion($args['question_id']);

        if (!$view['question']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['open_ended'] = $view['question']->question_multiple_choice == 0;
        $view['answers'] = $Gdpr->getAnswers($args['question_id'], $_SESSION['school_id']);
        $view['school_answer'] = $Gdpr->getQuestionAnswer($_SESSION['school_id'], $args['question_id']);
        
        $view['category_count'] = $Gdpr->getCategoryCount($view['question']->category_id);
        $view['last_question'] = $Gdpr->getLastQuestion();

        $view['progress'] = round((($view['question']->question_sort - 1) / $view['category_count']) * 100);
        
        $question = $Gdpr->getQuestion($args['question_id']);
        $last_question = $Gdpr->getLastQuestion();
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
                $next_question = $Gdpr->getQuestionByCategoryId($i, $j);

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

            $redirect = $this->router->pathFor('gdprDetails', ['question_id' => $next_question_id]);
        }
        
        $view['next_url'] = $redirect;

        return $this->view->render($res, 'gdprDetails.html', $view);
    })->setName('gdprDetails');
    
    $this->post('/{question_id}/create', function($req, $res, $args) use($app) {
        $Gdpr = new Gdpr($this);
        $School = new School($this);

        $data = $req->getParsedBody();

        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if ($data['file']) {
            $school_answer = $Gdpr->getQuestionAnswer($_SESSION['school_id'], $args['question_id']);

            if ($school_answer->file_url) {
                $this->logger->debug('Deleting file.', [ 'file_url' => $school_answer->file_url ]);

                $delete_file = $this->uploader->getFile($school_answer->file_url);
                $delete_file->delete();
            }
        }

        $Gdpr->purge($_SESSION['school_id'], $args['question_id']);
        
        $question = $Gdpr->getQuestion($args['question_id']);

        if($data['answer'] || $data['comment'] || $data['file']){
            
            if($data['file'])
                $url = $this->uploader->getFile($data['file'])->getUrl();
            
            if($question->question_multiple_choice)
                $Gdpr->create($_SESSION['school_id'], $req->getAttribute('user_id'), $args['question_id'], $data['answer'], $url, '', $data['comment']);
            else
                $Gdpr->create($_SESSION['school_id'], $req->getAttribute('user_id'), $args['question_id'], 0, $url, $data['answer'], $data['comment']);
        }

        $last_question = $Gdpr->getLastQuestion();
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
                $next_question = $Gdpr->getQuestionByCategoryId($i, $j);

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

            $redirect = $this->router->pathFor('gdprDetails', ['question_id' => $next_question_id]);
        }

        return $res->withStatus(302)->withHeader('Location', $redirect);
    })->setName('gdprCreate');
    
    $this->get('/{question_id}/file/delete', function($req, $res, $args) use($app) {
        $Gdpr = new Gdpr($this);
        $School = new School($this);
        
        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Internal error.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }
        
        $question = $Gdpr->getQuestion($args['question_id']);
        
        $school_answer = $Gdpr->getQuestionAnswer($_SESSION['school_id'], $args['question_id']);

        if ($school_answer->file_url) {
            $this->logger->debug('Deleting file.', [ 'file_url' => $school_answer->file_url ]);

            $delete_file = $this->uploader->getFile($school_answer->file_url);
            $delete_file->delete();
        }
        
        $redirect = $this->router->pathFor('gdprDetails', ['question_id' => $next_question_id]);
        
        return $res->withStatus(302)->withHeader('Location', $redirect);
    })->setName('gdprFileDelete');
    
});