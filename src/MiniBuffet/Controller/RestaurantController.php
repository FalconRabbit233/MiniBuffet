<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\RestController;

class RestaurantController extends RestController
{
    public function info()
    {
        $info = Manager::table('angaben')
            ->first();

        $this->responseJson($info);
    }
}
