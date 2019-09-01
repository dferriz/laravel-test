<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Worker;

use App\Console\Services\DataImporter\Crawler\Reader\EndpointReaderInterface;
use App\Console\Services\DataImporter\Crawler\Storer\EndpointStorerInterface;
use App\Models\Endpoint;
use App\Repository\EndpointRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * Class EndpointWorker
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   4/08/19 22:08
 * @package App\Console\Service\DataImporter\Worker\Crawler
 */
final class EndpointWorker implements EndpointWorkerInterface
{
    private $endpointReader;
    private $endpointStorer;
    private $entityManager;
    private $style;

    public function __construct(
        EndpointReaderInterface $endpointReader,
        EndpointStorerInterface $endpointStorer,
        EntityManagerInterface $entityManager,
        StyleInterface $style
    ) {
        $this->endpointReader = $endpointReader;
        $this->endpointStorer = $endpointStorer;
        $this->entityManager = $entityManager;
        $this->style = $style;
    }

    public function process()
    {

        /** @var EndpointRepository $endpointRepository */
        $endpointRepository = $this->entityManager->getRepository(Endpoint::class);

        $currentPage = 1;
        do {
            $paginator = $endpointRepository->findPaginatedBy($currentPage, ['entity'=>Endpoint::ENTITY_CATEGORY], [], 1);
            /** @var \Traversable $endpoints */
            $endpoints = $paginator->getResults();

            $productsEndpoints = $this->endpointReader->read($endpoints);
            $this->endpointStorer->store($productsEndpoints);

            $currentPage++;
        } while( $currentPage <= $paginator->getLastPage() );
    }

}
