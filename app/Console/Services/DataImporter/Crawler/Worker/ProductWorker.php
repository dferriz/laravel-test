<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Worker;

use App\Console\Services\DataImporter\Crawler\Storer\ProductStorerInterface;
use App\Console\Services\DataImporter\Crawler\Reader\ProductReaderInterface;
use App\Models\Endpoint;
use App\Repository\EndpointRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ProductWorker
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   4/08/19 19:34
 * @package App\Console\Service\DataImporter\Worker\Crawler
 */
final class ProductWorker implements ProductWorkerInterface
{
    private $productReader;
    private $productStorer;
    private $entityManager;
    
    public function __construct(
        ProductReaderInterface $productReader,
        ProductStorerInterface $productStorer,
        EntityManagerInterface $entityManager
    ) {
        $this->productReader = $productReader;
        $this->productStorer = $productStorer;
        $this->entityManager = $entityManager;
    }


    public function process() {
        
        /** @var EndpointRepository $endpointRepository */
        $endpointRepository = $this->entityManager->getRepository(Endpoint::class);

        $currentPage = 1;
        do {
            $paginator = $endpointRepository->findPaginatedBy($currentPage, ['entity'=>Endpoint::ENTITY_PRODUCT], [], 100);
            /** @var \Traversable $endpoints */
            $endpoints = $paginator->getResults();

            $productsDTO = $this->productReader->read($endpoints);
            $this->productStorer->store($productsDTO);

            $currentPage++;
        } while( $currentPage <= $paginator->getLastPage() );


    }
}
