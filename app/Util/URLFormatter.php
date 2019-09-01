<?php
declare(strict_types=1);

namespace App\Util;

/**
 * Class URLFormatter
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   2/08/19 20:13
 * @package App\Util
 */
class URLFormatter
{
    public static function removeQueryString($url) {

        return preg_replace('/\?.*/', '', $url);
    }
}