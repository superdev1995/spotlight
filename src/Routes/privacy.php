<?php

$app->get('/privacy', function($req, $res, $args) use($app) {
    $view['title'] = 'Privacy Policy';

    return $this->view->render($res, 'privacy.html', $view);
})->setName('privacy');
