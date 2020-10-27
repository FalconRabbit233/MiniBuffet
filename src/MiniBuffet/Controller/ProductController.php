<?php


namespace MiniBuffet\Controller;


use MiniBuffet\RestController;
use MiniBuffet\Service\ProductService;
use Slim\Slim;

class ProductController extends RestController
{
    /** @var ProductService */
    protected $product_service;

    /**
     * ProductController constructor.
     * @param Slim $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->product_service = new ProductService($app);
    }

    public function getAll()
    {
        $this->responseJson($this->product_service->getProcessedProducts());
    }

    public function getDrinks()
    {
        $this->responseJson(
            array_filter(
                $this->product_service->getProcessedProducts(),
                function ($item) {
                    return $item['isDrink'];
                })
        );
    }

    public function getDishes()
    {
        $this->responseJson(
            array_filter(
                $this->product_service->getProcessedProducts(),
                function ($item) {
                    return !$item['isDrink'];
                })
        );
    }
}
