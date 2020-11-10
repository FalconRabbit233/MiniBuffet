<?php


namespace MiniBuffet\Controller;


use MiniBuffet\Exception\BuffetItemAmountOverLimitException;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\Exception\ParamRequiredException;
use MiniBuffet\RestController;
use MiniBuffet\Service\CartService;
use MiniBuffet\Service\OrderService;

class CartController extends RestController
{
    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->app->container->get('MiniBuffet\Service\OrderService');
    }

    /**
     * @return CartService
     */
    protected function getCartService()
    {
        return $this->app->container->get('MiniBuffet\Service\CartService');
    }

    /**
     * @param int $orderId
     * @throws EntityNotFoundException
     */
    public function getByOrderId($orderId)
    {
        $order = $this->getOrderService()->getRawOrderById($orderId);

        $cart = $this->getCartService()
            ->getCartByOrderIdWithProcessedProducts($orderId);

        $this->responseJson($cart);
    }

    /**
     * @param int $orderId
     * @throws EntityNotFoundException
     * @throws ParamRequiredException
     */
    public function addByOrderId($orderId)
    {
        $order = $this->getOrderService()->getRawOrderById($orderId);

        $req = $this->readJson();

        self::checkRequired($req, array('ART_ID', 'amount'));

        $product = $req;

        $this->getCartService()->changeCartProduct($orderId, $product);

        $feedback = $this->getCartService()
            ->getCartByOrderIdWithProcessedProducts($orderId);

        $this->responseJson($feedback);
    }

    /**
     * @param int $orderId
     * @throws EntityNotFoundException
     * @throws ParamRequiredException
     */
    public function decreaseByOrderId($orderId)
    {
        $order = $this->getOrderService()->getRawOrderById($orderId);

        $req = $this->readJson();

        self::checkRequired($req, array('ART_ID', 'amount'));

        $product = $req;

        $product['amount'] = 0 - $product['amount'];

        $this->getCartService()->changeCartProduct($orderId, $product);

        $feedback = $this->getCartService()
            ->getCartByOrderIdWithProcessedProducts($orderId);

        $this->responseJson($feedback);
    }

    /**
     * @param int $orderId
     * @throws EntityNotFoundException
     * @throws BuffetItemAmountOverLimitException
     */
    public function submitToOrder($orderId)
    {
        $order = $this->getOrderService()->getRawOrderById($orderId);

        $products = $this->getCartService()->getRawCartByOrderId($orderId);

        $added_products = $this->getOrderService()->addProducts($orderId, $products);

        $products_prepared_to_remove = array_map(
            function ($item) {
                $item['amount'] = 0 - $item['amount'];
                return $item;
            },
            $added_products
        );

        $this->getCartService()->changeCartProducts($orderId, $products_prepared_to_remove);

        $feedback = array(
            'cart' => $this->getCartService()
                ->getCartByOrderIdWithProcessedProducts($orderId),
            'order' => $this->getOrderService()->getProcessedOrderById($orderId)
        );

        $this->responseJson($feedback);
    }
}
