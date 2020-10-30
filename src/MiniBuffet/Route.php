<?php


namespace MiniBuffet;


use Slim\Slim;

class Route
{
    /**
     * @param Slim $app
     */
    public static function run($app)
    {
        $app->group('/connTest', function () use ($app) {
            $conn_test_controller = $app->container->get('MiniBuffet\Controller\ConnectionTestController');

            $app->get('/', array($conn_test_controller, 'testAll'));
        });

        $app->group('/init', function () use ($app) {
            $init_controller = $app->container->get('MiniBuffet\Controller\InitController');

            $app->get('/', array($init_controller, 'run'));
        });

        $app->group('/tables', function () use ($app) {
            $table_controller = $app->container->get('MiniBuffet\Controller\TableController');

            $app->get('/', array($table_controller, 'getAll'));
            $app->get('/:id', array($table_controller, 'getById'));
            $app->get('/:id/currentOrder', array($table_controller, 'getCurrentOrderById'));
            $app->post('/:id/login', array($table_controller, 'loginById'));
            $app->post('/:id/start', array($table_controller, 'startById'));
        });

        $app->group('/products', function () use ($app) {
            $product_controller = $app->container->get('MiniBuffet\Controller\ProductController');

            $app->get('/', array($product_controller, 'getAll'));
            $app->get('/drinks', array($product_controller, 'getDrinks'));
            $app->get('/dishes', array($product_controller, 'getDishes'));
        });

        $app->group('/restaurant', function () use ($app) {
            $restaurant_controller = $app->container->get('MiniBuffet\Controller\RestaurantController');

            $app->get('/info', array($restaurant_controller, 'info'));
        });

    }
}
