<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Client;

/**
 * Interface ClientInterface
 *
 * @author David Ferriz Candela <dev@dferriz.es>
 * @since  31/07/19 1:05
 * @package App\Console\Service\DataImporter\Base\Client
 */
interface ClientInterface
{
    public function get($endpoint, $params = []);
}
