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
        $this->responseJson(
            array_filter(
                $this->getProductService()->getProcessedProducts(),
                function ($item) {
                    return $item['isDrink'];
                })
        );
    }

    public function getDishes()
    {
        $this->responseJson(
            array_filter(
                $this->getProductService()->getProcessedProducts(),
                function ($item) {
                    return !$item['isDrink'];
                })
        );
    }
}
