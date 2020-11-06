<?php


namespace MiniBuffet\Controller;


use MiniBuffet\RestController;
use MiniBuffet\Service\ProductService;

class ProductController extends RestController
{
    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->app->container->get('MiniBuffet\Service\ProductService');
    }

    public function getAll()
    {
        $this->responseJson($this->getProductService()->getProcessedProducts());
    }

    public function getDrinks()
    {
        $result = array();
        foreach ($this->getProductService()->getProcessedProductsInGroup() as $item) {
            if ($this->getProductService()->isGroupDrink($item['Gruppe'])) {
                $result[] = $item;
            }
        }

        $this->responseJson($result);
    }

    public function getDishes()
    {
        $result = array();
        foreach ($this->getProductService()->getProcessedProductsInGroup() as $item) {
            if (!$this->getProductService()->isGroupDrink($item['Gruppe'])) {
                $result[] = $item;
            }
        }

        $this->responseJson($result);
    }
}
