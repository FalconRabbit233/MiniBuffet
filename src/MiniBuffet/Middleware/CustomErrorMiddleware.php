<?php


namespace MiniBuffet\Middleware;


use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\Exception\RouteNotFoundException;
use Slim\Middleware;

class CustomErrorMiddleware extends Middleware
{
    /**
     * @inheritDoc
     */
    public function call()
    {
        $app = $this->app;
        try {
            $this->next->call();
        } catch (EntityNotFoundException $exception) {
            $app->response->headers->set('Content-type', 'application/json');
            $app->response->setStatus(404);
            $app->container->set('restResponse', array(
                'msg' => "{$exception->getMessage()} nicht gefunden"
            ));
        } catch (RouteNotFoundException $exception) {
            $app->response->headers->set('Content-type', 'application/json');
            $app->response->setStatus(404);
            $app->container->set('restResponse', array(
                'msg' => "{$app->request->getPath()} nicht gefunden"
            ));
        }
    }
}
