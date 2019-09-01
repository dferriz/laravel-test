<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Reader;

/**
 * Interface ReaderInterface
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   31/07/19 0:33
 * @package App\Console\Service\DataImporter\Base\Reader
 */
interface ReaderInterface
{
    public function read(\Traversable $endpoints);
}
