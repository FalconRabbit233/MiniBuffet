<?php


namespace MiniBuffet\Middleware;


use Slim\Middleware;

class JsonResponseMiddleware extends Middleware
{

    /**
     * @inheritDoc
     */
    public function call()
    {
        $this->next->call();

        $app = $this->app;

        $res_content_type = $app->response->headers->get('Content-type');

        if ($res_content_type == 'application/json') {
            $rest_res = $app->container->get('restResponse');

            $app->response->setBody(json_encode($rest_res));
        }
    }
}
