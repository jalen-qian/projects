<?php

$router = new Phalcon\Mvc\Router();
$router->setDefaultNamespace('MyApp\Controllers\Admin');
$router->setDefaultController('index');
$router->setDefaultAction('index');

$router->add('/:controller/:action', [
    'namespace'  => 'MyApp\Controllers',
    'controller' => 1,
    'action'     => 2,
]);

$router->add('/admin/:controller/:action', [
    'namespace'  => 'MyApp\Controllers\Admin',
    'controller' => 1,
    'action'     => 2,
]);

$router->add('/api/:controller/:action', [
    'namespace'  => 'MyApp\Controllers\Api',
    'controller' => 1,
    'action'     => 2,
    //'params' => 3,
]);

$router->add('/front/:controller/:action/:params', [
    'namespace'  => 'MyApp\Controllers\Front',
    'controller' => 1,
    'action'     => 2,
    'params'     => 3,
]);

return $router;
