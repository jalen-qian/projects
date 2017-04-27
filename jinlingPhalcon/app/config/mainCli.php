<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */

use MyApp\Library\Medoo;
use Phalcon\Cache\Backend\Redis as RedisCache;
use Phalcon\Cache\Frontend\Data as FrontData;
use Phalcon\Di\FactoryDefault\Cli as CliDi;
use Phalcon\Logger\Adapter\File as FileAdapter;

$di = new CliDi();

$di->setShared('config', function () use ($config) {
    return $config;
});

$di->setShared('logger', function () use ($config) {

    return new FileAdapter($config->logger->application);
});

//orm层不用phalcon自带的，改用pdo封装类库Medoo

$di->setShared('db', function () use ($config) {
    $dbConfig = $config->database->toArray();
    $dns      = [
        'database_type' => $dbConfig['adapter'],
        'database_name' => $dbConfig['dbname'],
        'server'        => $dbConfig['host'],
        'username'      => $dbConfig['username'],
        'password'      => $dbConfig['password'],
        'charset'       => $dbConfig['charset'],
        'port'          => $dbConfig['port'],
    ];
    unset($dbConfig);
    $db = new Medoo($dns);
    return $db;
});

$di->setShared('cache', function () use ($config) {
    // Cache data for one hour
    $frontCache = new FrontData(
        [
            "lifetime" => $config->redis->lifetime,
        ]
    );
    // Create the component that will cache "Data" to a "RedisCache" backend
    // RedisCache connection settings
    $cache = new RedisCache(
        $frontCache,
        [
            "servers" => [
                [
                    "host" => $config->redis->host,
                    "port" => $config->redis->port,
                    "auth" => $config->redis->auth,
                ],
            ],
        ]
    );
    return $cache;
});

$di->get('dispatcher')->setDefaultNamespace('MyApp\Tasks');
$di->get('dispatcher')->setNamespaceName('MyApp\Tasks');
