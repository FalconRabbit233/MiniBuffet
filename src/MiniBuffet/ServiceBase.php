<?php


namespace MiniBuffet;


use Slim\Slim;

class ServiceBase
{
    protected $app;

    /**
     * ServiceBase constructor.
     * @param Slim $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }
}
