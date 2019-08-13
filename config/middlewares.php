<?php

use \Slim\Exception\NotFoundException;


/**
 * Slim treats a URL pattern with a trailing slash as different to one without.
 * This middleware redirects all URLs that end in a / to the non-trailing /
 * equivalent.
 */
$app->add(function($req, $res, $next) {
    $uri = $req->getUri();
    $path = $uri->getPath();

    if ($path != '/' && substr($path, -1) == '/') {
        /**
         * Permanently redirect paths with a trailing slash to their
         * non-trailing counterpart.
         */
        $uri = $uri->withPath(substr($path, 0, -1));

        $this->logger->info('Redirect to non-trailing slash.', [ 'uri' => $uri ]);

        if ($req->isGet()) {
            return $res->withStatus(302)->withRedirect((string)$uri);
        } else {
            return $next($req->withUri($uri), $res);
        }
    }

    return $next($req, $res);
});

$app->add(function($req, $res, $next) {
    $view = $this->view->getEnvironment();

    /**
     * Make all configuration settings available to all views.
     */
    $view->addGlobal('settings', $this->get('settings'));

    if (isset($_COOKIE['auth_token'])) {
        $Auth = new Auth($this);
        $AuthToken = new AuthToken($this);


        $token = $Auth->validateToken($_COOKIE['auth_token']);

        $this->logger->debug('Validating auth_token cookie.', [ 'auth_token' => $_COOKIE['auth_token'] ]);

        /**
         * If the token was validated, we want to make the user object is
         * globally available in all controllers via $this->user.
         */
        if ($token->user_id) {

            if(isset($_SESSION['school_id'])){
                $check_role = $AuthToken->getRole($_SESSION['school_id'], $token->user_id);
                $view->addGlobal('user_role', $check_role);
            } 
            /**
             * Globalize the user object available to all views.
             */
            $view->addGlobal('user', $token);
            $view->addGlobal('user_id', $token->user_id);
            

            $req = $req->withAttribute('user', $token);
            $req = $req->withAttribute('user_id', $token->user_id);

            $this->logger->debug('Registered template globals.', [ 'user_id' => $token->user_id ]);
        }
    }

    return $next($req, $res);
});


/**
 * Here we globally ensure that unauthorized visitors will be promptly
 * redirected. All other page-specific exceptions are handled within the
 * respective controllers.
 */
$app->add(function($req, $res, $next) {
    $Auth = new Auth($this);
    $School = new School($this);

    $route = $req->getAttribute('route');

    if (empty($route)) {
        $this->logger->info('Route is empty.');
        throw new NotFoundException($req, $res);
    } else {
        $this->logger->info('Loading route.', [ 'route' => $route->getName() ]);
    }

    if ($req->isGet()) {
        if (isset($_COOKIE['auth_token'])) {
            $this->logger->debug('auth_token cookie found.', [ 'auth_token' => $_COOKIE['auth_token'] ]);

            $token = $Auth->validateToken($_COOKIE['auth_token']);
            
        }

        /**
         * If a GET route is not defined as publicly accessible, we should proceed
         * to a user authentication check.
         */
        if (!in_array($route->getName(), $this->get('settings')['view']['public']) && !in_array($route->getName(), $this->get('settings')['view']['task'])) {
            
            if (!$token) {
                $this->logger->debug('Redirecting because Auth::validateToken failed.', [ 'route' => $route->getName() ]);

                return $res->withStatus(302)->withRedirect($this->router->pathFor('login'));
            } else {
                $this->logger->debug('Auth::validateToken successful.', [ 'user_id' => $token->user_id ]);
            }
        }

        /**
         * We want to redirect expired subscriptions to the billing page and disallow
         * any other pages outside of the restricted views.
         */
        if(isset($token)){
            if ($token->user_type == 'T') {
                $this->logger->debug('User is a teacher.', [ 'user_id' => $token->user_id ]);

                if(isset($_SESSION['school_id'])){
                    $school_user = $School->getUser($_SESSION['school_id'], $token->user_id);
                    $req = $req->withAttribute('school_user', $school_user);
                }
                
            } else {
                $this->logger->debug('User is a parent.', [ 'user_id' => $token->user_id ]);
            }
        }
        

        if (isset($_SESSION['school_id'])) {
            if (!$School->getSubscriptionStatus($_SESSION['school_id'])) {
                $this->logger->debug('Subscription has expired.', [ 'school_id' => $_SESSION['school_id'] ]);

                if ($school_user->role == 1) {
                    if (!in_array($route->getName(), $this->get('settings')['view']['restricted'])) {
                        $this->flash->addMessage('danger', 'The subscription has expired. Please enter your billing information.');
                        $this->logger->debug('Redirecting because route not restricted.', [ 'route' => $route->getName() ]);

                        return $res->withStatus(302)->withRedirect($this->router->pathFor('billing'));
                    }
                } else {
                    return $res->withStatus(302)->withRedirect($this->router->pathFor('billingExpired'));
                }
            }
        } else {
            $this->logger->debug('School ID not defined.');
        }
    }

    return $next($req, $res);
});

/**
 * Slim uses the optional standalone slimphp/Slim-Csrf component to protect our
 * application from CSRF (cross-site request forgery).
 */
$app->add($container->get('csrf'));

//$app->add($container->get('el'));


/**
 * Do a CSRF verification check if the HTTP method is POST. Having this done
 * in a middleware saves us the check in every controller function.
 */
$app->add(function($req, $res, $next) {
    if ($req->isPost()) {
        if ($req->getAttribute('csrf_status') === false) {
            $this->logger->error('CSRF failure.');
            $this->flash->addMessage('danger', 'Oops! We redirected you back to the homepage because something weird happened.');

            return $res->withStatus(302)->withRedirect($this->router->pathFor('home'));
        }
    }

    return $next($req, $res);
});
