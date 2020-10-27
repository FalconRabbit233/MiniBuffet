<?php


namespace MiniBuffet;


use MiniBuffet\Controller\ConnectionTestController;
use MiniBuffet\Controller\InitController;
use MiniBuffet\Controller\TableController;
use Slim\Slim;

class Route
{
    /**
     * @param Slim $app
     */
    public static function run($app)
    {
        $app->group('/connTest', function () use ($app) {
            $conn_test_controller = new ConnectionTestController($app);

            $app->get('/', array($conn_test_controller, 'testAll'));
        });

        $app->group('/init', function () use ($app) {
            $init_controller = new InitController($app);

            $app->get('/', array($init_controller, 'run'));
        });

        $app->group('/tables', function () use ($app) {
            $table_controller = new TableController($app);

            $app->get('/', array($table_controller, 'getAll'));
            $app->get('/:id', array($table_controller, 'getById'));
            $app->get('/:id/currentOrder', array($table_controller, 'getCurrentOrderById'));
            $app->post('/:id/login', array($table_controller, 'loginById'));
            $app->post('/:id/start', array($table_controller, 'startById'));
        });
    }
}
