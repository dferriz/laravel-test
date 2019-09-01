<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Reader;

use App\Console\Services\DataImporter\Crawler\Client\ClientInterface;
use App\Models\Endpoint;
use App\Util\URLFormatter;
use App\ValueObject\EndpointVO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class EndpointReader
 *
 * @author David Ferriz Candela <dev@dferriz.es>
 * @since  31/07/19 0:32
 * @package App\Console\Service\DataImporter\Crawler\Reader
 */
final class EndpointReader implements EndpointReaderInterface
{
    private $entityManager;
    private $client;
    private $style;
    private $productsEndpoints = [];

    private $firstPage = 1;
    private $lastPage = 1;
    private $currentPage = 1;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClientInterface $client,
        StyleInterface $symfonyStyle
    )
    {
        $this->entityManager = $entityManager;
        $this->client = $client;
        $this->style  = $symfonyStyle;

    }

    /**
     * Read all category endpoints to extract products endpoints.
     *
     * @param \Traversable $categoryEndpoints
     *
     * @return array
     */
    public function read(\Traversable $categoryEndpoints) :array
    {
        $this->productsEndpoints = [];

        foreach($categoryEndpoints as $endpoint){
            /** @var Endpoint $endpoint */
            $this->firstPage = $this->currentPage = $this->lastPage = 1;
            $this->style->text(vsprintf('Reading <info>products endpoints</info> from <info>%s</info>', [$endpoint->getEndpoint()]));
            do {
                // Call client endpoint
                $rawData = $this->client->get($endpoint->getEndpoint(), ['page' => $this->currentPage]);
                $endpoint->setIsChecked(true);
                $endpoint->setLastCheck(time());
                $this->entityManager->persist($endpoint);


                $this->extractPagination($rawData);
                $this->extractProductsEndpoints($rawData);

                if ($this->firstPage == $this->currentPage)
                    $this->style->progressStart($this->lastPage);


                $this->style->progressAdvance();

                $this->currentPage++;
                if ( $this->currentPage > $this->currentPage )
                    $this->style->progressFinish();

            } while ($this->currentPage <= $this->lastPage);
            $this->entityManager->flush();
            $this->style->text('<info>Ok</info>');
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $this->productsEndpoints;
    }

    /**
     * Extract usesfull pagination data (max product pages) and set its in lastPage property.
     *
     * @param $rawData
     */
    private function extractPagination($rawData) {
        $crawler = new Crawler($rawData);

        // Extract pagination data if first loop.
        if ( $this->currentPage == 1 ) {

            $paginationNode = $crawler->filterXPath('//p[contains(@class, \'result-list-pagination\')]');

            $lastPageHref = $paginationNode->filterXPath('//a')->last()->attr('href');
            $query = parse_url($lastPageHref, PHP_URL_QUERY);
            parse_str($query, $params);

            $this->lastPage = $params['page'];
        }
    }

    private function extractProductsEndpoints($rawData) {

        $crawler = new Crawler($rawData);

        // Product extraction
        $productSection = $crawler->filterXPath('//section[contains(@class, \'search-results-section\')]');
        $productsCrawler = $productSection->filterXPath('//div[contains(@class, \'search-results-product\')]');
        $productsCrawler->each(
            function(Crawler $node)
            {
                $dirtyUrl   = $node->filterXPath('//div[contains(@class, \'product-image\')]/a')->attr('href');
                $url        = URLFormatter::removeQueryString($dirtyUrl);
                $productId  = intval(basename($url));

                $endpoint = new EndpointVO(Endpoint::ENTITY_PRODUCT, $productId, $url);

                array_push($this->productsEndpoints, $endpoint);

            });
    }

}
