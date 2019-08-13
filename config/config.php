<?php

$config = [
    /**
     * The mode can be development, test, production and is invoked with the
     * configMode() application method.
     */
    'debug'                                 => true,
    'mode'                                  => 'development',
    'determineRouteBeforeAppMiddleware'     => true, // Only set this if you need access to route within middleware.
    'displayErrorDetails'                   => true, // Disply errors should be set to false in production mode.
    'addContentLengthHeader'                => false,

    'feePerChild'                           => 1,

    'db' => [
        'driver'                            => 'mysql',
        'host'                              => 'localhost',
        'port'                              => '3306',
        'username'                          => 'root',
        'password'                          => 'mysql',
        'database'                          => 'teachkloud',
        'charset'                           => '',
        'collation'                         => '',
        'prefix'                            => '',
    ],

    'view' => [
        'path'                              => '../src/Views',
        'public'                            => [ 'terms', 'privacy', 'login', 'register', 'registerWelcome', 'registerConfirm', 'loginRecover', 'loginReset', 'parentInvite', 'stripe' ], // List of routes that are publicly accessible to visitors.
        'restricted'                        => [ 'home', 'account', 'accountEdit', 'accountEmail', 'accountPassword', 'schoolCreate', 'schoolSelect', 'school', 'schoolEdit', 'billing', 'billingEdit', 'billingSubscribe', 'billingUnsubscribe' ], // List of routes that are accessible to non-payers.
        'task'                              => [ 'taskExpiry', 'taskExpiryPost' ], // List of routes that are cronjobs.
    ],

    'logger' => [
        'name'                              => 'starlight',
        'level'                             => Monolog\Logger::INFO, // May want to change this to Monolog\Logger::INFO in production.
        'path'                              => '../logs/app.log',
    ],

    'mailer' => [
        'domain'                            => 'localhost',
        'host'                              => 'smtp.mailtrap.io',
        'port'                              => 2525,
        'username'                          => '',
        'password'                          => '',
        'html'                              => true,
        'smtpAuth'                          => true,
        'charSet'                           => 'UTF-8',
        'smtpSecure'                        => 'tls',
        'fromName'                          => 'TeachKloud',
        'from'                              => 'support@mg.teachkloud.com',
        'replyTo'                           => 'support@teachkloud.com',
    ],

    'uploader' => [
        'publicKey'                         =>  '46d3517169765b89caf7', //'d94c3b7c5ad176c2dfc7', // 74e16ecca432bf1f3cb8 // 703ed12f13e5935cfb44
        'secretKey'                         => '03b934852f2698b19d5b' //'ae08000db10a35277162', // 669e6cfcc2dcd7aad17d // 154e72d94158d651c03d
    ],

    'stripe' => [
        'publicKey'                         => 'pk_test_IVBFgBNjheK8fYBGJtlvMpAH', // pk_live_Lyjrgh65TjuPpHs9OKcAEK8S
        'secretKey'                         => 'sk_test_7SyAsq3eb3E7E71ucEuElFOF', //
    ],
];