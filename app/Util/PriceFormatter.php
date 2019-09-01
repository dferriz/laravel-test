<?php
declare(strict_types=1);

namespace App\Util;

/**
 * Class PriceFormatter
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   1/08/19 20:19
 * @package App\Util
 */
class PriceFormatter
{
    public static function getFloatedPrice($plainPrice) {

        preg_match('/(\d+(?:(\.|\,)\d{1,2})?)/', $plainPrice, $matches);
        $priceFormatted = preg_replace('/(?:\,)/',  '.', $matches[0]);

        $priceFloated = floatval($priceFormatted);

        return $priceFloated;
    }
}