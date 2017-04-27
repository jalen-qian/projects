<?php
/**
 *注册服务类
 */

use MyApp\Library\Medoo;
use MyApp\Library\XCookies;
use MyApp\Plugins\NotFoundPlugin;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as Session;
//use Phalcon\Flash\Direct as FlashDirect;

$di = new FactoryDefault();

$di->setShared('config', function () use ($config) {
    return $config;
});

$di->set('dispatcher', function () {
    $eventsManager = new EventsManager;
    /**
     * Check if the user is allowed to access certain action using the SecurityPlugin
     */
    //$eventsManager->attach('dispatch:beforeDispatch', new SecurityPlugin);//开启权限控制
    /**
     * Handle exceptions and not-found exceptions using NotFoundPlugin
     */
    //$eventsManager->attach('dispatch:beforeException', new NotFoundPlugin);

    $dispatcher = new Dispatcher;
    $dispatcher->setEventsManager($eventsManager);
    $dispatcher->setDefaultNamespace('MyApp\Controllers');
    return $dispatcher;
});

/**
 * Setting up the Router
 */
$di->set('router', function () {
    return require __DIR__ . '/routes.php';
}, true);
/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

// Start the session the first time when some component request the session service
$di->setShared('session', function () {
    $session = new Session();
    $session->start();
    return $session;
});

/**
 * Setting up the view component
 */
$di->setShared('view', function () use ($config) {

    $view = new View();

    $view->setViewsDir($config->application->viewsDir);

    $view->registerEngines([
        '.volt'  => function ($view, $di) use ($config) {

            $volt = new VoltEngine($view, $di);

            $volt->setOptions([
                'compiledPath'      => $config->application->cacheDir,
                'compiledSeparator' => '_',
                'stat'              => true,
                'compileAlways'     => true,
            ]);

            return $volt;
        },
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php',
    ]);

    return $view;
});

$di->setShared('logger', function () use ($config) {

    return new FileAdapter($config->logger->application);
});

//orm层不用phalcon自带的，改用pdo封装类库Medoo
$di->setShared('db', function () use ($config) {
    $dbConfig = $config->database->toArray();

    $dns = [
        'database_type' => $dbConfig['adapter'],
        'database_name' => $dbConfig['dbname'],
        'server'        => $dbConfig['host'],
        'username'      => $dbConfig['username'],
        'password'      => $dbConfig['password'],
        'charset'       => $dbConfig['charset'],
        'port'          => $dbConfig['port'],
    ];
    $db = new Medoo($dns);

    //$db = new EasyDB($dbConfig);
    unset($dbConfig);
    return $db;
});
// $di->setShared('db2', function () use ($config) {
//     $dbConfig = $config->database->toArray();

//     $dns = [
//         'database_type' => $dbConfig['adapter'],
//         'database_name' => $dbConfig['dbname'],
//         'server'        => $dbConfig['host'],
//         'username'      => $dbConfig['username'],
//         'password'      => $dbConfig['password'],
//         'charset'       => $dbConfig['charset'],
//         'port'          => $dbConfig['port'],
//     ];
//     $db = new \Medoo\Medoo($dns);

//     //$db = new EasyDB($dbConfig);
//     unset($dbConfig);
//     return $db;
// });

$di->set('flash', function () {
    return new Flash(array(
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning',
    ));
});
//缓存不使用phalcon自带的，改用原生Redis类库
$di->setShared('cache', function () use ($config) {

    //Create a Data frontend and set a default lifetime to 1 hour
    /* $frontend = new Phalcon\Cache\Frontend\Data(array(
    'lifetime' => $config->redis->lifetime,
    ));

    //Create the cache passing the connection
    $cache = new Phalcon\Cache\Backend\Redis($frontend, array(
    'host' => $config->redis->host,
    'port' => $config->redis->port,
    //'auth'       => 'foobared',
    //'persistent' => false,
    ));*/

    $cache = new Redis();
    $cache->connect($config->redis->host,$config->redis->port,$config->redis->lifetime);

    return $cache;
});

$di->set('cookies', function () {
    $cookies = new XCookies();
    return $cookies;
});
