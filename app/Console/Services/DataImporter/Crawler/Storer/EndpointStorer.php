<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Storer;

use App\Models\Endpoint;
use App\Repository\EndpointRepository;
use App\ValueObject\EndpointVO;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * Class EndpointStorer
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   1/08/19 23:36
 * @package App\Console\Service\DataImporter\Crawler\Storer
 */
final class EndpointStorer implements EndpointStorerInterface
{
    /** @var $entityManager EntityManager */
    private $entityManager;
    private $style;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        StyleInterface $style,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->style = $style;
        $this->logger = $logger;
    }


    /**
     * Stores all collected products endpoints to the database.
     *
     * @param array $endpointsVO
     */
    public function store(array $endpointsVO)
    {
        $this->style->section('Store <info>PRODUCT</info> endpoints on database');

        /** @var $repository EndpointRepository */
        $repository = $this->entityManager->getRepository(Endpoint::class);

        foreach($endpointsVO as $endpointVO) {
            /** @var $endpointVO EndpointVO */

            $endpoint = $repository->findOneBy([
                'entity' => Endpoint::ENTITY_PRODUCT,
                'entityId' => $endpointVO->getEntityId()
            ]);

            if (!$endpoint instanceof Endpoint ){
                $endpoint = new Endpoint();
                $endpoint->setEntity(Endpoint::ENTITY_PRODUCT);
                $endpoint->setEntityId($endpointVO->getEntityId());
            }

            $endpoint->setIsChecked(true);
            $endpoint->setEndpoint($endpointVO->getEndpoint());

            try {
                $this->style->text(vsprintf("Storing product <info>%s</info> endpoint <info>%s</info>", [$endpointVO->getEntityId(), $endpointVO->getEndpoint()]));
                $this->entityManager->persist($endpoint);
                $this->entityManager->flush();
                $this->style->text('<info>Ok</info>');
            } catch ( \Exception $e ) {
                $this->logger->error($e->getMessage());
                $this->style->warning(vsprintf("Endpoint entity %s failed on persist ", [$endpoint->getEntityId()]));
            }
        }
    }

}
