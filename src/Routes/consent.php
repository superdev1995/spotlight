<?php

$app->get('/consent', function($req, $res, $args) use($app) {
    $view['title'] = 'Consent';

    return $this->view->render($res, 'consent.html', $view);
})->setName('consent');
