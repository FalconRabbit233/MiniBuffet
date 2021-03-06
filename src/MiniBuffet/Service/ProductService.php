<?php


namespace MiniBuffet\Service;


use Illuminate\Database\Capsule\Manager;
use MiniBuffet\Exception\EntityNotFoundException;
use MiniBuffet\ServiceBase;
use MiniBuffet\Utils;

class ProductService extends ServiceBase
{

    /**
     * @return array
     */
    public function getRawGroups()
    {
        static $raw_groups;

        if ($raw_groups === null) {
            $raw_groups = Manager::table('wgruppen')
                ->select(array('*'))
                ->get();
        }

        return $raw_groups;
    }

    /**
     * @return array
     */
    public function getRawDrinkGroups()
    {
        static $raw_drink_groups;

        if ($raw_drink_groups === null) {
            $raw_drink_groups = Manager::table('buffet_drink_group')
                ->select(array('*'))
                ->get();
        }

        return $raw_drink_groups;
    }

    /**
     * @return array
     */
    public function getRawProducts()
    {
        static $raw_products;

        if ($raw_products === null) {
            $raw_products = Manager::table('s_karte')
                ->select(array('*'))
                ->get();
        }

        return $raw_products;
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
                $rawProduct['image'] = $rawProduct['image'] == null ?
                    null :
                    Utils::getRouteUri($this->app, $rawProduct['image']);

                $processed_products[] = $rawProduct;
            }

        }

        return $processed_products;
    }

    public function getProcessedProductsInGroup()
    {
        $grouped_processed_product = array();
        foreach ($this->getProcessedProducts() as $processedProduct) {
            $group_id = $processedProduct['Gruppen'];
            if (isset($grouped_processed_product[$group_id])) {
                $grouped_processed_product[$group_id]['items'][] = $processedProduct;
            }else{
                $group = $this->getGroupByGroupId($group_id);
                $group['items'] = array($processedProduct);
                $grouped_processed_product[$group_id] = $group;
            }
        }

        return array_values($grouped_processed_product);
    }

    /**
     * @param int $id
     * @return mixed
     * @throws EntityNotFoundException
     */
    public function getProcessedProductById($id)
    {
        static $artId_processedProducts_dict;

        if ($artId_processedProducts_dict === null) {
            $artId_processedProducts_dict = Utils::listToDict(
                $this->getProcessedProducts(),
                function ($item) {
                    return $item['ART_ID'];
                }
            );
        }

        if (!isset($artId_processedProducts_dict[$id])) {
            throw new EntityNotFoundException("Gericht($id)");
        }

        return $artId_processedProducts_dict[$id];
    }
}
