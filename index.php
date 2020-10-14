<?php

include_once __DIR__ . '/vendor/autoload.php';

use MiniBuffet\Exception\RouteNotFoundException;
use MiniBuffet\Middleware\CustomErrorMiddleware;
use MiniBuffet\Middleware\JsonResponseMiddleware;
use MiniBuffet\Route;
use Slim\Slim;
use Illuminate\Database\Capsule\Manager;


$app = new Slim(array(
    'debug' => true,

));

$app->container->set('env', require __DIR__ . '/env.php');

$app->add(new CustomErrorMiddleware());
$app->add(new JsonResponseMiddleware());

$app->notFound(function () use ($app) {
    throw new RouteNotFoundException();
});

$manager = new Manager();
$manager->addConnection($app->env['db']);
$manager->setAsGlobal();
$manager->bootEloquent();

Route::run($app);

$app->run();
