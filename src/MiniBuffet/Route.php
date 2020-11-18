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
            $app->post('/:id/callWaiter', array($table_controller, 'callWaiterById'));
            $app->post('/:id/callCheckOut', array($table_controller, 'callCheckOutById'));
        });

        $app->group('/products', function () use ($app) {
            $product_controller = $app->container->get('MiniBuffet\Controller\ProductController');

            $app->get('/', array($product_controller, 'getAll'));
            $app->get('/drinks', array($product_controller, 'getDrinks'));
            $app->get('/dishes', array($product_controller, 'getDishes'));
        });

        $app->group('/orders', function () use ($app) {
            $order_controller = $app->container->get('MiniBuffet\Controller\OrderController');

            $app->get('/:id', array($order_controller, 'getById'));
            $app->get('/:id/ready', array($order_controller, 'checkReadyToAddDish'));
            $app->post('/:id', array($order_controller, 'addDishesToOrder'));
            $app->post('/:id/close', array($order_controller, 'closeById'));

            $cart_controller = $app->container->get('MiniBuffet\Controller\CartController');
            $app->get('/:orderId/cart', array($cart_controller, 'getByOrderId'));
            $app->post('/:orderId/cart/change', array($cart_controller, 'changeByOrderId'));
            $app->post('/:orderId/cart/changeMany', array($cart_controller, 'changeManyByOrderId'));
            $app->post('/:orderId/cart/submit', array($cart_controller, 'submitToOrder'));
            $app->post('/:orderId/cart/clear', array($cart_controller, 'clearCartByOrderId'));

        });

        $app->group('/restaurant', function () use ($app) {
            $restaurant_controller = $app->container->get('MiniBuffet\Controller\RestaurantController');

            $app->get('/info', array($restaurant_controller, 'info'));
        });

    }
}
