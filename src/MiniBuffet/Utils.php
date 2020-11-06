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

    /**
     * @param \Slim\Slim $app
     * @param string $relPath
     * @return string
     */
    public static function getRouteUri($app, $relPath)
    {
        return <<<URL
{$app->env['protocol']}://{$app->env['root']}/{$app->env['appDir']}/$relPath
URL;

    }
}
