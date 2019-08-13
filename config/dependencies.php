<?php

use \Slim\Views\Twig;
use \Slim\Views\TwigExtension;
use \Slim\Csrf\Guard;
use \Slim\Flash\Messages;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;
//use \PHPMailer\PHPMailer;
//use \Uploadcare;


/**
 * The idea of the dependency injection container (DIC) is that you configure
 * the container to be able to load the dependencies that your application
 * needs, when it needs them.
 */
$container = $app->getContainer();


$container['logger'] = function($c) {
    $config = $c->get('settings')['logger'];

    $logger = new Logger($config['name']);
    $file_handler = new StreamHandler($config['path']);

    $logger->pushHandler($file_handler);

    return $logger;
};

$container['db'] = function($c) {
    $settings = $c->get('settings')['db'];

    $pdo = new PDO($settings['driver'].':host='.$settings['host'].';dbname='.$settings['database'].';port='.$settings['port'].';charset='.$settings['charset'], $settings['username'], $settings['password']);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    return $pdo;
};

/**
 * Eloquent ORM
 */
$container['el'] = function ($c) {
	$capsule = new \Illuminate\Database\Capsule\Manager;
	$capsule->addConnection($c->get('settings')['db']);

	$capsule->setAsGlobal();
	$capsule->bootEloquent();

	return $capsule;
};

$container['view'] = function($c) {
    $config = $c->get('settings')['view'];

    $view = new Twig($config['path'], [
        'cache' => false,
        'debug' => $c->get('settings')['debug'],
    ]);

    $gravatar = new Twig_SimpleFilter('gravatar', function($email) {
        $hashedEmail = md5(strtolower($email));

        $str = 'https://www.gravatar.com/avatar/'.$hashedEmail.'?d=identicon';

        return $str;
    });

		$view->getEnvironment()->addFilter($gravatar);
		
		/**
		 * File Exists
		 */
		$file_exists = new Twig_SimpleFunction('file_exists', function ($path) {
			return file_exists($path);
		});
		
		$view->getEnvironment()->addFunction($file_exists);

    /**
     * Instantiate and add Slim specific extension.
     */
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new TwigExtension($c['router'], $basePath));
    $view->addExtension(new Csrf($c['csrf']));
    $view->addExtension(new Twig_Extension_Debug());

	/**
	 * Add current year and month to the view, so that we can access it for proper URL routing
	 * in master.dashboard
	 */
    $view['current_year'] = date('Y');
    $view['current_month'] = date('m');

    return $view;
};

$container['flash'] = function($c) {
    return new Messages;
};

$container['csrf'] = function($c) {
    $guard = new Guard();

    $guard->setPersistentTokenMode(true);
    $guard->setFailureCallable(function($req, $res, $next) {
        $req = $req->withAttribute('csrf_status', false);

        return $next($req, $res);
    });

    return $guard;
};

$container['uploader'] = function($c) {
    $config = $c->get('settings')['uploader'];

    return new \Uploadcare\Api($config['publicKey'], $config['secretKey']);
};
$container['autocompleter'] = function($c){
    $config = $c->get('settings')['autocompleter'];
    $c = new \Autocompleter($config['url']);
    return $c;
};

$container['mailer'] = function($c) {
    $config = $c->get('settings')['mailer'];

	$mailer = new \PHPMailer;

	$mailer->Host = $config['host'];
	$mailer->Port = $config['port'];
	$mailer->Username = $config['username'];
	$mailer->Password = $config['password'];
	$mailer->isHTML($config['html']);
	$mailer->isSMTP();
	$mailer->CharSet = $config['charSet'];
	$mailer->SMTPAuth = $config['smtpAuth'];
	$mailer->SMTPSecure = $config['smtpSecure'];
	$mailer->SetFrom($config['from'], $config['fromName']);
	$mailer->AddReplyTo($config['replyTo'], $config['fromName']);

	return new Mailer($c->view, $mailer, $c->logger);
};

$container['notFoundHandler'] = function($c) {
    return function($req, $res) use($c) {
        $view['title'] = 'Page Not Found (404)';

        $c['logger']->info('Page was not found.', ['URI' => $req->getUri()]);

        return $c['view']->render($res->withStatus(404), '404.html', $view);
    };
};

$container['notAllowedHandler'] = function($c) {
    return function($req, $res, $methods) use($c) {
        $view['title'] = 'Method Not Allowed (405)';
        $view['methods'] = implode(', ', $methods);

        $c['logger']->info('Method is not allowed.');

        return $c['view']->render($res->withStatus(405)->withHeader('Allow', implode(', ', $methods)), '405.html', $view);
    };
};
