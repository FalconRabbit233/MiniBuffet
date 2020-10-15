<?php


namespace MiniBuffet;


use MiniBuffet\Exception\EnumException;
use MiniBuffet\Exception\ParamNullException;
use MiniBuffet\Exception\ParamRequiredException;
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

    /**
     * @param array $request
     * @param string[] $params
     * @throws ParamRequiredException
     */
    protected static function checkRequired($request, $params)
    {
        $missing_params = array();
        foreach ($params as $param) {
            if (!isset($request[$param])) {
                $missing_params[] = $param;
            }
        }

        if (count($missing_params) > 0) {
            throw new ParamRequiredException(implode(', ', $missing_params));
        }
    }

    /**
     * @param array $request
     * @param string[] $params
     * @throws ParamNullException
     */
    protected static function checkNotNull($request, $params)
    {
        $null_params = array();
        foreach ($params as $param) {
            if ($request[$param] === null) {
                $null_params[] = $param;
            }
        }

        if (count($null_params) > 0) {
            throw new ParamNullException(implode(', ', $null_params));
        }
    }

    /**
     * @param $enum
     * @param $set
     * @param string $name
     * @throws EnumException
     */
    protected static function checkEnum($enum, $set, $name = 'Enum')
    {
        if (!in_array($enum, $set)) {
            $set_str = implode(', ', $set);
            throw new EnumException("$name($enum) nicht verfügbar. Soll: $set_str");
        }
    }
}
