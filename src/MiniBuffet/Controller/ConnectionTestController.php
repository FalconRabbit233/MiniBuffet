<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\RestController;

class ConnectionTestController extends RestController
{
    public function testAll()
    {
        $result = Manager::connection()->select('select current_timestamp');
        $this->responseJson($result);
    }
}
