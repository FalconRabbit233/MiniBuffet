<?php


namespace MiniBuffet;


use MiniBuffet\Controller\CartController;
use MiniBuffet\Controller\ConnectionTestController;
use MiniBuffet\Controller\InitController;
use MiniBuffet\Controller\OrderController;
use MiniBuffet\Controller\ProductController;
use MiniBuffet\Controller\RestaurantController;
use MiniBuffet\Controller\TableController;
use MiniBuffet\Service\CartService;
use MiniBuffet\Service\OrderService;
use MiniBuffet\Service\ProductService;
use MiniBuffet\Service\TableService;
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

        $app->container->singleton(
            'MiniBuffet\Controller\CartController',
            function () use ($app) {
                return new CartController($app);
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

        $app->container->singleton(
            'MiniBuffet\Service\TableService',
            function () use ($app) {
                return new TableService($app);
            }
        );

        $app->container->singleton(
            'MiniBuffet\Service\CartService',
            function () use ($app) {
                return new CartService($app);
            }
        );
        // endregion
    }
}
