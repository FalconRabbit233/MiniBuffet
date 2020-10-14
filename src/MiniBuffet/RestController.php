<?php


namespace MiniBuffet;


use Slim\Slim;

class RestController
{
    protected $app;

    /**
     * RestController constructor.
     * @param Slim $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @return mixed
     */
    protected function readJson()
    {
        return json_decode($this->app->request->getBody(), 'true');
    }

    /**
     * @param array $arr
     */
    protected function responseJson($arr)
    {
        $this->app->response->headers->set('Content-type', 'application/json');
        $this->app->container->set('restResponse', $arr);
    }
}
