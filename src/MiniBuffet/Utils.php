<?php


namespace MiniBuffet;


class Utils
{
    /**
     * @param array $list
     * @param callable $keySelector
     * @return array
     */
    public static function listToDict($list, $keySelector)
    {
        $dict = array();
        foreach ($list as $item) {
            $dict[$keySelector($item)] = $item;
        }
        return $dict;
    }

    /**
     * @param float $price
     * @return float
     */
    public static function priceFormatFloat($price)
    {
        return (float)number_format($price, 2, '.', '');
    }
}
