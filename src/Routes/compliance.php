<?php

$app->group('/compliance', function() use($app) {
    $this->get('', function($req, $res, $args) use($app) {
        $Compliance = new Compliance($this);
        $School = new School($this);
        $Country = new Country($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Compliance Self-Audit';
        $view['countries_not_available'] = $Country->getCountriesNotAvailable();

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['categories'] = $Compliance->getCategories($view['school']->country_id);
        $view['first_question'] = $Compliance->getFirstQuestionByCountry($view['school']->country_id);

        return $this->view->render($res, 'compliance.html', $view);
    })->setName('compliance');

    $this->get('/summary', function($req, $res, $args) use($app) {
        $Compliance = new Compliance($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Compliance Self-Audit Summary';

        if (!$School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'))) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $view['school'] = $School->getOne($_SESSION['school_id']);
        $view['categories'] = $Compliance->getCategories($view['school']->country_id);
        $view['questions'] = $Compliance->getQuestions();

        $relevant_question_count = 0;

        foreach($view['questions'] as $question) {
            $is_relevant_question = false;

            if ($question->school_categories) {
                if (in_array($view['school']->category_id, explode(',', $question->school_categories))) {
                    $relevant_question_count++;
                    $is_relevant_question = true;
                }
            } else {
                $relevant_question_count++;
                $is_relevant_question = true;
            }

            if ($is_relevant_question) {
                $answers = $Compliance->getAnswers($question->question_id, $_SESSION['school_id']);

                $correct_questions[$question->question_id] = false;

                foreach($answers as $answer) {
                    if (!$answer->created_at) {
                        continue;
                    }

                    if ($answer->answer_id == $question->answer_id) {
                        $correct_questions[$question->question_id] = false;

                        continue;
                    }

                    if ($question->question_multiple_choice) {
                        if (count($answers) != $Compliance->getSchoolAnswerCount($_SESSION['school_id'], $question->question_id)) {
                            continue;
                        }
                    }

                    $correct_questions[$question->question_id] = true;
                }

                $view['answers'][$question->question_id] = $answers;
            }
        }

        $view['score'] = round(count(array_filter($correct_questions)) / $relevant_question_count, 2);
        $view['correct_questions'] = $correct_questions;

        return $this->view->render($res, 'complianceSummary.html', $view);
    })->setName('complianceSummary');

    $this->post('/reset', function($req, $res, $args) use($app) {
        $Compliance = new Compliance($this);
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

        $Compliance->purgeAll($_SESSION['school_id']);

        $this->logger->info('Compliance survey reset.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
        $this->flash->addMessage('success', 'The survey has been reset.');

        return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('compliance'));
    })->setName('complianceReset');

    $this->get('/{question_id}', function($req, $res, $args) use($app) {
        $Compliance = new Compliance($this);
        $School = new School($this);

        $view['flash'] = $this->flash->getMessages();
        $view['title'] = 'Compliance Self-Audit';

        $school_user = $School->getUser($_SESSION['school_id'], $req->getAttribute('user_id'));

        if (!$school_user) {
            $this->logger->info('School::getUser failed.', ['school_id' => $_SESSION['school_id'], 'user_id' => $req->getAttribute('user_id')]);
            $this->flash->addMessage('danger', 'You don’t have sufficient rights.');

            return $res->withStatus(302)->withHeader('Location', $this->router->pathFor('home'));
        }

        $school = $School->getOne($_SESSION['school_id']);

        $view['categories'] = $Compliance->getCategories($school->country_id);
        $view['question'] = $Compliance->getQuestion($args['question_id']);
        $view['question_code'] = $Compliance->getQuestionCode($args['question_id']);
        $view['school'] = $School->getOne($_SESSION['school_id']);
        if (!$view['question']) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        /**
         * This check is to ensure that only questions linked to a category of
         * the own country is permitted.
         */
        foreach ($view['categories'] as $category) {
            $permitted_categories[] = $category->category_id;
        }

        if (!in_array($view['question']->category_id, $permitted_categories)) {
            $notFoundHandler = $this->get('notFoundHandler');

            return $notFoundHandler($req, $res);
        }

        $view['answers'] = $Compliance->getAnswers($args['question_id'], $_SESSION['school_id']);
        $view['category_count'] = $Compliance->getCategoryCount($view['question']->category_id);
        $view['last_question'] = $Compliance->getLastQuestion();

        $view['progress'] = round((($view['question']->question_sort - 1) / $view['category_count']) * 100);

        return $this->view->render($res, 'complianceDetails.html', $view);
    })->setName('complianceDetails');

    $this->post('/{question_id}/create', function($req, $res, $args) use($app) {
        $Compliance = new Compliance($this);
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

        foreach ($data['answers'] as $k => $v) {
            if ($data['uploads'][$k]) {
                $school_answer = $Compliance->getSchoolAnswer($_SESSION['school_id'], $v);

                if ($school_answer->file_url) {
                    $this->logger->debug('Deleting file.', [ 'file_url' => $school_answer->file_url ]);

                    $delete_file = $this->uploader->getFile($school_answer->file_url);
                    $delete_file->delete();
                }
            }
        }

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
    })->setName('complianceCreate');
});
