<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Worker;

/**
 * Interface EndpointWorkerInterface
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   4/08/19 22:09
 * @package App\Console\Service\DataImporter\Base\Worker
 */
interface EndpointWorkerInterface
{
    function process();
}
