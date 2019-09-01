<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Base\Storer;

/**
 * Interface ProductStorerInterface
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   2/08/19 13:36
 * @package App\Console\Service\DataImporter\Base\Storer
 */
interface ProductStorerInterface
{
    function store(array $products);
}
