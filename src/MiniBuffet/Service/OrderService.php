<?php


namespace MiniBuffet\Service;


use Illuminate\Database\Capsule\Manager;
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
            $raw_details['product'] = $this->getProductService()
                ->getProcessedProductById($raw_detail['ART_ID']);
            $processed_details[] = $raw_detail;
        }

        $raw_order = $this->getRawOrderById($id);

        $raw_order['details'] = $processed_details;

        return $raw_order;
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
