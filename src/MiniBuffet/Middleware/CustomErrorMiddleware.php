<?php


namespace MiniBuffet\Middleware;


use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\Exception\EnumException;
use MiniBuffet\Exception\ParamNullException;
use MiniBuffet\Exception\ParamRequiredException;
use MiniBuffet\Exception\RouteNotFoundException;
use Slim\Middleware;

class CustomErrorMiddleware extends Middleware
{
    /**
     * @param mixed $code
     * @param array $response
     */
    public function errorInJson($code, $response)
    {
        $app = $this->app;
        $app->response->headers->set('Content-type', 'application/json');
        $app->response->setStatus($code);
        $app->container->set('restResponse', $response);
    }

    /**
     * @inheritDoc
     */
    public function call()
    {
        $app = $this->app;
        try {
            $this->next->call();
        } catch (EntityNotFoundException $exception) {
            $this->errorInJson(404, array(
                'msg' => "{$exception->getMessage()} nicht gefunden"
            ));
        } catch (RouteNotFoundException $exception) {
            $this->errorInJson(404, array(
                'msg' => "{$app->request->getPath()} nicht gefunden"
            ));
        } catch (ParamRequiredException $exception) {
            $this->errorInJson(400, array(
                'msg' => "folgenden parametern nicht gefunden:[{$exception->getMessage()}]"
            ));
        } catch (ParamNullException $exception) {
            $this->errorInJson(400, array(
                'msg' => "folgenden parametern sollen nicht null sein:[{$exception->getMessage()}]"
            ));
        } catch (EnumException $exception) {
            $this->errorInJson(400, array(
                'msg' => $exception->getMessage()
            ));
        }
    }
}
