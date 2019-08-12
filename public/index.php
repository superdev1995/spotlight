<?php

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');


require_once '../vendor/autoload.php';


require_once '../vendor/dompdf/dompdf/src/Autoloader.php';
use Dompdf\Dompdf;


/**
 * Sessions are required for handling flash messages. They may come in handy
 * for other operations.
 */
session_start();


/**
 * We can add configurations to our application when it's created. They are
 * stored in config/config.php and were included above so they can be passed
 * as a parameter here.
 */
require_once '../config/config.php';
$app = new \Slim\App(['settings' => $config]);


/**
 * Most applications will have some dependencies, and Slim handles them nicely
 * using a DIC (Dependency Injection Container) built on Pimple.
 */
require_once '../config/dependencies.php';


/**
 * You can run code before and after your Slim application to manipulate the
 * Request and Response objects as you see fit. This is called middleware.
 */
require_once '../config/middlewares.php';


/**
 * Routes are configured in config/routes.php. Slim’s router is built on top of
 * the nikic/FastRoute component, and it is remarkably fast and stable.
 */
if ($handle = opendir('../src/Routes')) {
    while (false !== ($file = readdir($handle))) {
        if ('.' === $file || '..' === $file || substr($file, -4) !== '.php') continue;
        require_once '../src/Routes/'.$file;
    }

    closedir($handle);
}

if ($handle = opendir('../src/Routes/API')) {
    while (false !== ($file = readdir($handle))) {
        if ('.' === $file || '..' === $file || substr($file, -4) !== '.php') continue;
        require_once '../src/Routes/API/'.$file;
    }

    closedir($handle);
}

$app->run();
