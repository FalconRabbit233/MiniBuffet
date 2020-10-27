<?php


namespace MiniBuffet\Service;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\ServiceBase;
use MiniBuffet\Utils;

class ProductService extends ServiceBase
{
    /** @var array */
    private static $raw_groups;

    /** @var array */
    private static $raw_drink_groups;

    /** @var array */
    private static $raw_products;

    /**
     * @return array
     */
    public function getRawGroups()
    {
        if (self::$raw_groups === null) {
            self::$raw_groups = Manager::table('wgruppen')
                ->select(array('*'))
                ->get();
        }

        return self::$raw_groups;
    }

    /**
     * @return array
     */
    public function getRawDrinkGroups()
    {
        if (self::$raw_drink_groups === null) {
            self::$raw_drink_groups = Manager::table('buffet_drink_group')
                ->select(array('*'))
                ->get();
        }

        return self::$raw_drink_groups;
    }

    /**
     * @return array
     */
    public function getRawProducts()
    {
        if (self::$raw_products === null) {
            self::$raw_products = Manager::table('s_karte')
                ->select(array('*'))
                ->get();
        }

        return self::$raw_products;
    }

    /**
     * @param $group_id
     * @return array
     */
    public function getGroupByGroupId($group_id)
    {
        static $group_id_group_dict;

        if ($group_id_group_dict === null) {
            $group_id_group_dict = Utils::listToDict($this->getRawGroups(), function ($item) {
                return $item['Gruppe'];
            });
        }

        return $group_id_group_dict[$group_id];
    }

    /**
     * @param $group_id
     * @return bool
     */
    public function isGroupDrink($group_id)
    {
        static $drink_group_dict;

        if ($drink_group_dict === null) {
            $drink_group_dict = Utils::listToDict($this->getRawDrinkGroups(), function ($item) {
                return $item['groupId'];
            });
        }

        return isset($drink_group_dict[$group_id]);
    }

    /**
     * @return array
     */
    public function getProcessedProducts()
    {
        static $processed_products;

        if ($processed_products === null) {
            $processed_products = array();
            foreach ($this->getRawProducts() as $rawProduct) {
                $group_id = $rawProduct['Gruppen'];
                $rawProduct['group_info'] = $this->getGroupByGroupId($group_id);
                $rawProduct['isDrink'] = $this->isGroupDrink($group_id);
                $rawProduct['price'] = $rawProduct['isDrink'] ? $rawProduct['PREIS'] : 0;

                $processed_products[] = $rawProduct;
            }

        }

        return $processed_products;
    }
}
