<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Worker;

/**
 * Interface ProductWorkerInterface
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   4/08/19 19:34
 * @package App\Console\Service\DataImporter\Base\Worker
 */
interface ProductWorkerInterface
{
    public function process();
}
