<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Cache;

/**
 * Interface RequestCacheInterface
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   31/07/19 11:58
 * @package App\Console\Service\DataImporter\Base\Cache
 */
interface EndpointCacheInterface
{
    public function isCached($endpoint, $params) :bool;
    public function storeCache($endpoint, $params, $data) :void;
    public function retrieveCache($endpoint, $params) :string;
}
