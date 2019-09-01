<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Job;

use App\Repository\EndpointRepository;
use App\Repository\ProductRepository;
use App\Util\Timer;
use App\Console\Services\DataImporter\Crawler\Worker\EndpointWorkerInterface;
use App\Console\Services\DataImporter\Crawler\Worker\ProductWorkerInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * Class ImportCatalogJob
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   31/07/19 0:04
 * @package App\Console\Service\DataImporter\Base\Job
 */
final class ImportCatalogJob implements ImportCatalogJobInterface
{
    private $endpointProcessor;
    private $productProcessor;

    private $endpointRepository;
    private $productRepository;

    private $style;

    public function __construct(
        EndpointWorkerInterface $endpointProcessor,
        ProductWorkerInterface $productProcessor,
        EndpointRepository $endpointRepository,
        ProductRepository $productRepository,
        StyleInterface $style
    ) {

        $this->endpointProcessor = $endpointProcessor;
        $this->productProcessor = $productProcessor;
        $this->endpointRepository = $endpointRepository;
        $this->productRepository = $productRepository;

        $this->style = $style;
    }

    public function doJob()
    {
        Timer::getInstance()->start('job');
        $this->style->title('WELCOME TO CATALOG SYNCRONIZATION COMMAND!');

        $this->uncheckEntities();

        Timer::getInstance()->start('endpoint_processor');
        $this->endpointProcessor->process();
        Timer::getInstance()->end('endpoint_processor');


        Timer::getInstance()->start('product_processor');
        $this->productProcessor->process();
        Timer::getInstance()->end('product_processor');

        $this->deleteUncheckedEntities();
        Timer::getInstance()->end('job');
    }

    private function uncheckEntities() {
        $this->style->text('Setting isCheck field to false on all main entities...');
        $this->productRepository->uncheckAll();
        $this->endpointRepository->uncheckAll();
        $this->style->text('Ok');
    }


    private function deleteUncheckedEntities() {

        $this->style->section('Entity cleanup');
        $this->style->note('All outdated entities will be removed.');

        try
        {
            $this->productRepository->removeUnchecked();
            $this->endpointRepository->removeUnchecked();
        } catch(\Exception $e) {
            $this->style->error("There was a problem removing unchecked entities");
        }

        $this->style->text('<info>All outdated entities have been removed.</info>');
    }
}
