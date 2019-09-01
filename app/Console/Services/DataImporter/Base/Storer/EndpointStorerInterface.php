<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Storer;

/**
 * Interface EndpointStorerInterface
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   1/08/19 23:24
 * @package App\Console\Service\DataImporter\Base\Storer
 */
interface EndpointStorerInterface
{
    function store(array $endpoints);
}
