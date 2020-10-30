<?php

include_once __DIR__ . '/vendor/autoload.php';

use MiniBuffet\DI;
use MiniBuffet\Exception\RouteNotFoundException;
use MiniBuffet\Middleware\CustomErrorMiddleware;
use MiniBuffet\Middleware\JsonResponseMiddleware;
use MiniBuffet\Route;
use Slim\Slim;
use Illuminate\Database\Capsule\Manager;

date_default_timezone_set('utc');

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

$app->container->singleton('setting', function () use ($manager) {
    return $manager::table('buffet_setting')
        ->select(array('*'))
        ->first();
});

DI::run($app);
Route::run($app);

$app->run();
