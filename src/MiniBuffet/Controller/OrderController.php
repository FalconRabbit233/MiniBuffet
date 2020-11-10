<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\Exception\BuffetItemAmountOverLimitException;
use MiniBuffet\Exception\BuffetPasswordWrongException;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\Exception\OrderClosedException;
use MiniBuffet\Exception\ParamRequiredException;
use MiniBuffet\RestController;
use MiniBuffet\Service\OrderService;
use MiniBuffet\Service\ProductService;

class OrderController extends RestController
{
    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->app->container->get('MiniBuffet\Service\OrderService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->app->container->get('MiniBuffet\Service\ProductService');
    }

    /**
     * @param int $id
     * @throws EntityNotFoundException
     */
    public function getById($id)
    {
        $order = $this->getOrderService()->getProcessedOrderById($id);

        $this->responseJson($order);
    }

    /**
     * @param int $orderId
     * @throws ParamRequiredException
     * @throws EntityNotFoundException
     * @throws OrderClosedException
     * @throws BuffetItemAmountOverLimitException
     */
    public function addDishesToOrder($orderId)
    {
        $order = $this->getOrderService()->getProcessedOrderById($orderId);

        if ($order['closed']) {
            throw new OrderClosedException($orderId);
        }

        $request = $this->readJson();

        self::checkRequired($request, array('items'));

        $this->getOrderService()->addProducts($orderId, $request['items']);

        $feedback = $this->getOrderService()->getProcessedOrderById($orderId);

        $this->responseJson($feedback);
    }

    /**
     * @param int $id
     * @throws EntityNotFoundException
     * @throws ParamRequiredException
     * @throws BuffetPasswordWrongException
     */
    public function closeById($id)
    {
        $request = $this->readJson();

        self::checkRequired($request, array('password'));

        $feedback = $this->getOrderService()->closeByIdWithPassword($id, $request['password']);

        $this->responseJson($feedback);
    }

}
