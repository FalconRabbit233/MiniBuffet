<?php


namespace MiniBuffet;


use MiniBuffet\Controller\ConnectionTestController;
use MiniBuffet\Controller\InitController;
use MiniBuffet\Controller\OrderController;
use MiniBuffet\Controller\ProductController;
use MiniBuffet\Controller\RestaurantController;
use MiniBuffet\Controller\TableController;
use MiniBuffet\Service\OrderService;
use MiniBuffet\Service\ProductService;
use Slim\Slim;

class DI
{
    /**
     * @param Slim $app
     */
    public static function run($app)
    {
        // region -- controller --
        $app->container->singleton(
            'MiniBuffet\Controller\ConnectionTestController',
            function () use ($app) {
                return new ConnectionTestController($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Controller\InitController',
            function () use ($app) {
                return new InitController($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Controller\ProductController',
            function () use ($app) {
                return new ProductController($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Controller\OrderController',
            function () use ($app) {
                return new OrderController($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Controller\RestaurantController',
            function () use ($app) {
                return new RestaurantController($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Controller\TableController',
            function () use ($app) {
                return new TableController($app);
            }
        );
        // endregion

        // region -- service --
        $app->container->singleton(
            'MiniBuffet\Service\ProductService',
            function () use ($app) {
                return new ProductService($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Service\OrderService',
            function () use ($app) {
                return new OrderService($app);
            }
        );
        // endregion
    }
}
