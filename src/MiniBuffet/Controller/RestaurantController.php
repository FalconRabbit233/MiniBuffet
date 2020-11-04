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

        $opening_info = Manager::table('sf_table')
            ->get();

        $info['openingTime'] = $opening_info;  

        $this->responseJson($info);
    }
}
