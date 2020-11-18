<?php


namespace MiniBuffet\Service;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\ServiceBase;
use MiniBuffet\Utils;

class CartService extends ServiceBase
{
    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->app->container->get('MiniBuffet\Service\ProductService');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->app->container->get('MiniBuffet\Service\OrderService');
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function getRawCartByOrderId($orderId)
    {
        return Manager::table('buffet_cart')
            ->select(array('*'))
            ->where('orderId', '=', $orderId)
            ->get();
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function getCartByOrderIdWithProcessedProducts($orderId)
    {
        $raw_cart = $this->getRawCartByOrderId($orderId);

        $processed_cart = array();
        foreach ($raw_cart as $item) {
            try {
                $item['productInfo'] = $this->getProductService()
                    ->getProcessedProductById($item['ART_ID']);
            } catch (EntityNotFoundException    $exception) {
                continue;
            }

            $processed_cart[] = $item;
        }

        return $processed_cart;
    }

    /**
     * @param int $orderId
     * @return array
     * @throws EntityNotFoundException
     */
    public function getProcessedCart($orderId)
    {
        $cart = $this->getCartByOrderIdWithProcessedProducts($orderId);

        $order = $this->getOrderService()->getRawOrderById($orderId);

        $take_price_from_processed_product =
            $order['menuType'] == 'buffet' ?
                function ($processed_product) {
                    return $processed_product['price'];
                } :
                function ($processed_product) {
                    return $processed_product['PREIS'];
                };

        $cart_price = array_reduce(
            $cart,
            function ($carry, $item) use ($take_price_from_processed_product) {
                return $carry + $take_price_from_processed_product($item['productInfo']) * $item['amount'];
            },
            0
        );

        $formatted_price = Utils::priceFormatFloat($cart_price);

        return array(
            'cart' => $cart,
            'totalPrice' => $formatted_price
        );
    }

    /**
     * @param int $orderId
     * @param $product
     * @return int|null
     * @throws EntityNotFoundException
     */
    public function changeCartProduct($orderId, $product)
    {
        static $orderId_cartItems;
        if (!isset($orderId_cartItems[$orderId])) {
            $orderId_cartItems[$orderId] = Utils::listToDict(
                $this->getRawCartByOrderId($orderId),
                function ($item) {
                    return $item['id'];
                }
            );
        }

        $cartItems = $orderId_cartItems[$orderId];

        $this->getProductService()->getProcessedProductById($product['ART_ID']);

        if (!isset($product['amount'])) {
            $product['amount'] = 1;
        }

        if (!isset($product['attribute1Selected'])) {
            $product['attribute1Selected'] = 0;
        }

        if (!isset($product['attribute2Selected'])) {
            $product['attribute2Selected'] = 0;
        }

        $likely_product = null;
        foreach ($cartItems as $cartItem) {
            if (
                $cartItem['ART_ID'] == $product['ART_ID'] &&
                $cartItem['attribute1Selected'] == $product['attribute1Selected'] &&
                $cartItem['attribute2Selected'] == $product['attribute2Selected']
            ) {
                $likely_product = $cartItem;
                break;
            }
        }

        if ($likely_product !== null) {
            /** @var int $cart_id */
            $cart_id = $likely_product['id'];
            $new_amount = $product['amount'];
            $cartItems[$cart_id]['amount'] = $new_amount;

            if ($new_amount <= 0) {
                Manager::table('buffet_cart')
                    ->where('id', '=', $cart_id)
                    ->delete();
                unset($cartItems[$cart_id]);
            } else {
                Manager::table('buffet_cart')
                    ->where('id', '=', $cart_id)
                    ->update(array(
                        'amount' => $new_amount
                    ));
            }
        } else {
            if ($product['amount'] > 0) {
                $new_cart = array(
                    'orderId' => $orderId,
                    'ART_ID' => $product['ART_ID'],
                    'amount' => $product['amount'],
                    'attribute1Selected' => $product['attribute1Selected'],
                    'attribute2Selected' => $product['attribute2Selected'],
                );

                $cart_id = Manager::table('buffet_cart')
                    ->insertGetId($new_cart);

                $new_cart['id'] = $cart_id;

                $cartItems[$cart_id] = $new_cart;
            } else {
                $cart_id = null;
            }
        }

        return $cart_id;
    }

    /**
     * @param $orderId
     * @param $products
     */
    public function changeCartProducts($orderId, $products)
    {
        foreach ($products as $product) {
            try {
                $this->changeCartProduct($orderId, $product);
            } catch (EntityNotFoundException $exception) {
                continue;
            }
        }
    }

    /**
     * @param int $orderId
     */
    public function clearCart($orderId)
    {
        Manager::table('buffet_cart')
            ->where('orderId', '=', $orderId)
            ->delete();
    }
}
