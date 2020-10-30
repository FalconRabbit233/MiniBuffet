<?php


namespace MiniBuffet\Middleware;


use MiniBuffet\Exception\BuffetItemAmountOverLimitException;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\Exception\EnumException;
use MiniBuffet\Exception\OrderClosedException;
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
                'exception' => 'EntityNotFoundException',
                'msg' => "{$exception->getMessage()} nicht gefunden"
            ));
        } catch (RouteNotFoundException $exception) {
            $this->errorInJson(404, array(
                'exception' => 'RouteNotFoundException',
                'msg' => "{$app->request->getPath()} nicht gefunden"
            ));
        } catch (ParamRequiredException $exception) {
            $this->errorInJson(400, array(
                'exception' => 'ParamRequiredException',
                'msg' => "folgenden parametern nicht gefunden:[{$exception->getMessage()}]"
            ));
        } catch (ParamNullException $exception) {
            $this->errorInJson(400, array(
                'exception' => 'ParamNullException',
                'msg' => "folgenden parametern sollen nicht null sein:[{$exception->getMessage()}]"
            ));
        } catch (EnumException $exception) {
            $this->errorInJson(400, array(
                'exception' => 'EnumException',
                'msg' => $exception->getMessage()
            ));
        } catch (OrderClosedException $exception) {
            $this->errorInJson(400, array(
                'exception' => 'OrderClosedException',
                'msg' => "Bestellung({$exception->getMessage()}) ist schon beendet"
            ));
        } catch (BuffetItemAmountOverLimitException $exception) {
            $this->errorInJson(400, array(
                'exception' => 'BuffetItemAmountOverLimitException',
                'msg' => "Sie Können {$exception->getMessage()} Gerichte bestellen"
            ));
        }
    }
}
