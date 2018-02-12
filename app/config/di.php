<?php

use app\plugins\EventListener;
use app\services\FactoryDefault;
use app\services\OAuth2Session;
use app\services\OAuth2Storage;
use app\services\Router;
use OAuth2\GrantType\RefreshToken;
use OAuth2\GrantType\UserCredentials;
use OAuth2\Server as OAuth2Server;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Db\Adapter as PdoAdapter;
use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Events\Manager;
use Phalcon\Events\ManagerInterface;


/** @var Config|\stdClass $config */
$config = require __DIR__ . '/config.php';

// Show errors on development env
if (!empty($config->debug)) {
    if (!empty($config->debug->error_reporting)) {
        error_reporting($config->debug->error_reporting) ;
    }
    if (!empty($config->debug->display_errors) && strtolower($config->debug->display_errors) != 'off') {
        ini_set('display_errors', 'On');
    }
}

// Create custom dependency injector
$di = new FactoryDefault();

// Registering a config
$di->set('config', $config);


// Register custom router
$di->set('router', function() use ($di) {
    $router = new Router(false);
    $router->setEventsManager($di->get('eventsManager'));
    $router->removeExtraSlashes(true);

    // Mount smart routes
    $router->mountRoutes();
    return $router;
}, true);


// Open database connection
$di->set('db', function () use ($config) {
    echo '';
    echo '';
    return new Mysql([
        'host' => $config->database->host,
        'port' => $config->database->port,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname' => $config->database->dbname,
        'charset' => $config->database->charset
    ]);
}, true);


// OAuth2 Server for OAuth2 authentication
$di->set('oauth2server', function () use ($config) {
    // Create PDO storage for OAuth2
    $storage = new OAuth2Storage(
        [ // connection config
            'dsn' => 'mysql:host=' . $config->database->host . ';dbname=' . $config->database->dbname,
            'username' => $config->database->username,
            'password' => $config->database->password,
            'dbname' => $config->database->dbname,
            'charset' => $config->database->charset
        ],
        [ // tables
            'client_table' => 'oauth2_client',
            'access_token_table' => 'oauth2_access_token',
            'refresh_token_table' => 'oauth2_refresh_token',
            'scope_table'  => 'oauth2_scope',
        ]
    );

    // Run OAuth2 server
    $server = new OAuth2Server($storage,
        [ // config
            'issuer' => $_SERVER['HTTP_HOST'],
            'access_lifetime' => $storage->getLifetime(),
            'refresh_token_lifetime' => $storage->getLifetime(),
        ],
        [ // grant types
            'user_credentials' => new UserCredentials($storage),
            'refresh_token' => new RefreshToken($storage, ['always_issue_new_refresh_token' => true]),
        ]
    );

    return $server;
}, true);


// Register OAuth2 session
$di->set('session', function() use ($di) {
    return new OAuth2Session($di->get('oauth2server'));
}, true);


// Register events listener
/** @var Manager|ManagerInterface $eventsManager */
$eventsManager = $di->get('eventsManager');
$eventListener = new EventListener();
// Listen router events from events manager for run ACL access checks
$eventsManager->attach('router', $eventListener, 100);
