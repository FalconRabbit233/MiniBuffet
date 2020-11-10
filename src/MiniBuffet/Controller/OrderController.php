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

        $dishes_to_add = array();
        $drinks_to_add = array();
        foreach ($request['items'] as $product_info) {

            try {
                self::checkRequired($product_info, array('ART_ID', 'amount'));
                $product = $this->getProductService()
                    ->getProcessedProductById($product_info['ART_ID']);

                if (!$product) {
                    continue;
                }

                $product_info['detail'] = $product;

                if ($product['isDrink']) {
                    $drinks_to_add[] = $product_info;
                } else {
                    $dishes_to_add[] = $product_info;
                }
            } catch (ParamRequiredException $exception) {
                continue;
            }

        }

        $round_increase = 0;
        $using_buffet = $order['diningType'] == 'Buffet';
        if ($using_buffet) {
            $last_round_at = \DateTime::createFromFormat('Y-m-d H:i:s', $order['lastRoundAt']);

            $new_round_came = (new \DateTime()) >
                $last_round_at->add(new \DateInterval("PT{$order['timer']}M"));

            $reach_total_round = $order['currentRound'] >= $order['totalRound'];

            if (!$new_round_came || $reach_total_round) {
                $dishes_to_add = array();
            } else {
                $dish_total_amount = array_reduce(
                    $dishes_to_add,
                    function ($carry, $item) {
                        return $carry + (int)$item['amount'];
                    },
                    0
                );

                if ($dish_total_amount > $order['item']) {
                    throw new BuffetItemAmountOverLimitException("{$order['item']}(jetzt $dish_total_amount)");
                } elseif ($dish_total_amount > 0) {
                    $round_increase = 1;
                }
            }
        }

        $products_to_add = array_merge($dishes_to_add, $drinks_to_add);

        foreach ($products_to_add as $product_info) {
            $detail_insert_info = array(
                'ART_ID' => $product_info['detail']['ART_ID'],
                'orderId' => $orderId,
                'amount' => $product_info['amount'],
                'name' => $product_info['detail']['ART'],
                'price' => $using_buffet ?
                    $product_info['detail']['price'] :
                    $product_info['detail']['PREIS'],

                'round' => $order['currentRound'] + $round_increase,

                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),

            );

            if (isset($product_info['attribute1Selected']) && $product_info['attribute1Selected']) {
                $detail_insert_info ['attribute1Selected'] = (bool)$product_info['attribute1Selected'];
                $detail_insert_info['attr1'] = $product_info['detail']['ZUWAHLPOPUP1'];
            }

            if (isset($product_info['attribute2Selected']) && $product_info['attribute2Selected']) {
                $detail_insert_info ['attribute2Selected'] = (bool)$product_info['attribute2Selected'];
                $detail_insert_info['attr2'] = $product_info['detail']['ZUWAHLPOPUP2'];
            }

            Manager::table('buffet_order_detail')
                ->insert($detail_insert_info);
        }

        $new_raw_details = $this->getOrderService()->getRawDetailsByOrderId($orderId);

        $order_total_price = 0.0;
        foreach ($new_raw_details as $new_raw_detail) {
            $order_total_price += $new_raw_detail['price'] * $new_raw_detail['amount'];
        }

        $order_update_info = array(
            'updatedAt' => date('Y-m-d H:i:s'),
            'currentRound' => $order['currentRound'] + $round_increase,
            'totalPrice' => $order_total_price,

        );

        if ($round_increase) {
            $order_update_info['lastRoundAt'] = date('Y-m-d H:i:s');
        }

        Manager::table('buffet_order')
            ->where('id', '=', $orderId)
            ->update($order_update_info);

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
