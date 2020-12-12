<?php


namespace MiniBuffet\Controller;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\Exception\EnumException;
use MiniBuffet\Exception\ParamNullException;
use MiniBuffet\Exception\ParamRequiredException;
use MiniBuffet\RestController;
use MiniBuffet\Service\TableService;

class TableController extends RestController
{

    /**
     * @return TableService
     */
    protected function getTableService()
    {
        return $this->app->container->get('MiniBuffet\Service\TableService');
    }

    public function getAll()
    {
        $result = Manager::table('besitzen')
            ->selectRaw(<<<TEXT
besitzen.*,
(
    select count(id) 
    from buffet_order 
    where buffet_order.Tisch = besitzen.Tisch and
          buffet_order.closed = 0
) as orderExists
TEXT
            )
            ->orderBy('Reihenfolge')
            ->get();

        $result = array_map(function ($item) {
            $item['orderExists'] = (bool)$item['orderExists'];
            return $item;
        }, $result);

        $this->responseJson($result);
    }

    /**
     * @param $id
     * @throws EntityNotFoundException
     */
    public function getById($id)
    {
        $result = Manager::table('besitzen')
            ->selectRaw(<<<TEXT
besitzen.*,
(
    select count(id) 
    from buffet_order 
    where buffet_order.Tisch = besitzen.Tisch and
          buffet_order.closed = 0
) as orderExists
TEXT
            )
            ->where('besitzen.Tisch', '=', $id)
            ->first();

        if (!$result) {
            throw new EntityNotFoundException("Tisch($id)");
        }

        $result['orderExists'] = (bool)$result['orderExists'];

        $this->responseJson($result);
    }

    /**
     * @param $id
     * @throws EntityNotFoundException
     */
    public function getCurrentOrderById($id)
    {
        $table = $this->getTableService()->findTableById($id);

        $existing_order = Manager::table('buffet_order')
            ->select(array('*'))
            ->where('Tisch', '=', $id)
            ->where('closed', '=', 0)
            ->limit(1)
            ->first();

        $order_exists = (bool)$existing_order;

        $this->responseJson(array(
            'exists' => $order_exists,
            'info' => $existing_order
        ));
    }

    /**
     * @param $id
     * @throws EntityNotFoundException
     * @throws ParamRequiredException
     * @throws EnumException
     */
    public function loginById($id)
    {
        $table = $this->getTableService()->findTableById($id);

        $req = $this->readJson();

        $password = $this->app->setting['buffetPassword'];

        self::checkRequired($req, array('password', 'diningType'));

        $dining_type = $req['diningType'];

        self::checkEnum($dining_type, array('Buffet', 'a-la-carte'), 'Art');

        $input_password = $req['password'];

        $password_correct = $password == $input_password;

        $this->responseJson(array(
            'diningType' => $dining_type,
            'passwordCorrect' => $password_correct
        ));
    }

    /**
     * @param $id
     * @throws EntityNotFoundException
     * @throws ParamRequiredException
     * @throws ParamNullException
     * @throws EnumException
     */
    public function startById($id)
    {
        $table = $this->getTableService()->findTableById($id);

        $existing_order = Manager::table('buffet_order')
            ->select(array('id', 'totalRound'))
            ->where('Tisch', '=', $id)
            ->where('closed', '=', 0)
            ->limit(1)
            ->first();

        if ($existing_order) {
            $this->responseJson(array(
                'isNewOrder' => false,
                'orderId' => $existing_order['id'],
                'totalRound' => $existing_order['totalRound'],
            ));
            return;
        }

        $req = $this->readJson();

        self::checkRequired($req, array(
            'diningType', 'adultCount', 'childCount', 'timer', 'item', 'menuType'
        ));

        self::checkNotNull($req, array(
            'diningType', 'adultCount', 'childCount', 'timer', 'item', 'menuType'
        ));

        self::checkEnum($req['diningType'], array('Buffet', 'a-la-carte'), 'Art');
        self::checkEnum($req['menuType'], array('Mittagsessen', 'Abendessen', 'Spezial'), 'Menütyp');

        $total_round = $this->app->setting['totalRound'];
        $password = $this->app->setting['buffetPassword'];

        $order_id = Manager::table('buffet_order')
            ->insertGetId(array(
                'Tisch' => $id,
                'diningType' => $req['diningType'],
                'menuType' => $req['menuType'],
                'adultCount' => $req['adultCount'],
                'childCount' => $req['childCount'],
                'timer' => $req['timer'],
                'item' => $req['item'],
                'currentRound' => 1,
                'totalRound' => $total_round,
                'password' => $password,
                'createdAt' => date('Y-m-d H:i:s'),
                'updatedAt' => date('Y-m-d H:i:s'),
            ));

        $this->responseJson(array(
            'isNewOrder' => true,
            'orderId' => $order_id,
            'totalRound' => $total_round,
        ));
    }

    /**
     * @param $id
     * @throws EntityNotFoundException
     */
    public function callWaiterById($id)
    {
        $this->getTableService()->findTableById($id);

        // todo: do something to show customer request

        $this->responseJson(array(
            'succeed' => false
        ));
    }

    /**
     * @param $id
     * @throws EntityNotFoundException
     * @throws EnumException
     * @throws ParamRequiredException
     */
    public function callCheckOutById($id)
    {
        $this->getTableService()->findTableById($id);

        $request = $this->readJson();

        self::checkRequired($request, array('payType'));

        $pay_type = $request['payType'];

        self::checkEnum($pay_type, array('Bar', 'Karte'), 'Bezahlungsart');

        // todo: do something to show customer request

        $this->responseJson(array(
            'succeed' => false
        ));
    }
}
