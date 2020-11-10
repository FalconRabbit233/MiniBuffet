<?php


namespace MiniBuffet\Service;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\Exception\BuffetItemAmountOverLimitException;
use MiniBuffet\Exception\BuffetPasswordWrongException;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\ServiceBase;

class OrderService extends ServiceBase
{
    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->app->container->get('MiniBuffet\Service\ProductService');
    }

    /**
     * @param int $id
     * @return mixed
     * @throws EntityNotFoundException
     */
    public function getRawOrderById($id)
    {
        $raw_order = Manager::table('buffet_order')
            ->select(array('*'))
            ->where('id', '=', $id)
            ->first();

        if ($raw_order === null) {
            throw new EntityNotFoundException("Order($id)");
        }

        return $raw_order;
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function getRawDetailsByOrderId($orderId)
    {
        return Manager::table('buffet_order_detail')
            ->select(array('*'))
            ->where('orderId', '=', $orderId)
            ->get();
    }

    /**
     * @param int $id
     * @return array
     * @throws EntityNotFoundException
     */
    public function getProcessedOrderById($id)
    {
        $raw_details = $this->getRawDetailsByOrderId($id);

        $processed_details = array();
        foreach ($raw_details as $raw_detail) {
            try {
                $raw_details['product'] = $this->getProductService()
                    ->getProcessedProductById($raw_detail['ART_ID']);
            } catch (EntityNotFoundException $exception) {
                continue;
            }

            $processed_details[] = $raw_detail;
        }

        $raw_order = $this->getRawOrderById($id);

        $raw_order['details'] = $processed_details;

        return $raw_order;
    }

    /**
     * @param int $orderId
     * @param $products_info
     * @return array
     * @throws BuffetItemAmountOverLimitException
     * @throws EntityNotFoundException
     */
    public function addProducts($orderId, $products_info)
    {
        $order = $this->getProcessedOrderById($orderId);

        $dishes_to_add = array();
        $drinks_to_add = array();

        foreach ($products_info as $product_info) {
            if (!isset($product_info['ART_ID'])) {
                continue;
            }

            if (!isset($product_info['amount'])) {
                continue;
            }

            $product = $this->getProductService()
                ->getProcessedProductById($product_info['ART_ID']);

            $product_info['detail'] = $product;

            if ($product['isDrink']) {
                $drinks_to_add[] = $product_info;
            } else {
                $dishes_to_add[] = $product_info;
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

        $new_raw_details = $this->getRawDetailsByOrderId($orderId);

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

        return $products_to_add;
    }

    /**
     * @param int $id
     * @param string $password
     * @return array
     * @throws EntityNotFoundException
     * @throws BuffetPasswordWrongException
     */
    public function closeByIdWithPassword($id, $password)
    {
        $raw_order = $this->getRawOrderById($id);

        if ($raw_order['password'] != $password) {
            throw new BuffetPasswordWrongException();
        }

        Manager::table('buffet_order')
            ->where('id', '=', $id)
            ->update(array(
                'closed' => true,
                'updatedAt' => date('Y-m-d H:i:s'),

            ));

        return $this->getProcessedOrderById($id);
    }
}
