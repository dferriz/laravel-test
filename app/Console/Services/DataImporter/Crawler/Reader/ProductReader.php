<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Reader;

use App\Console\Services\DataImporter\Crawler\Client\ClientInterface;
use App\Models\Endpoint;
use App\Util\PriceFormatter;
use App\DataTransferObject\ProductDTO;
use App\Util\URLFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class ProductReader
 *
 * @author David Ferriz Candela <dev@dferriz.es>
 * @since  31/07/19 0:32
 * @package App\Console\Service\DataImporter\Crawler\Reader
 */
final class ProductReader implements ProductReaderInterface
{
    private $entityManager;
    private $client;
    private $style;

    public function __construct(
        EntityManagerInterface $entityManager,
        ClientInterface $client,
        StyleInterface $style
    )
    {
        $this->entityManager = $entityManager;
        $this->client = $client;
        $this->style = $style;
    }

    public function read(\Traversable $endpoints)
    {
        $this->style->section("Read <info>PRODUCT</info> endpoints");

        $products = [];

        foreach($endpoints as $endpoint)
        {
            /** @var Endpoint  $endpoint */
            try {
                $this->style->text(vsprintf("Reading product <info>%s</info> from <info>%s</info>", [$endpoint->getEntityId(), $endpoint->getEndpoint()]));
                // Call client endpoint
                $rawData = $this->client->get($endpoint->getEndpoint());
                // Store last call
                $endpoint->setLastCheck(time());
                $this->entityManager->persist($endpoint);
                $this->entityManager->flush();

                $productDTO = new ProductDTO($endpoint->getEntityId());
                $this->extractProductData($rawData, $productDTO);

                $this->style->text("<info>Ok</info>");
                array_push($products, $productDTO);
            } catch( \Exception $e ) {
                $this->style->error(vsprintf("Error extracting data from product '%s' at endpoint '%s', error message: %s", [$endpoint->getEntityId(), $endpoint->getEndpoint(), $e->getMessage()]));
            }
        }

        return $products;
    }

    private function extractProductData($rawData, $productDTO) {

        $crawler = new Crawler($rawData);

        $this->extractName($crawler, $productDTO);
        $this->extractPrices($crawler, $productDTO);
        $this->extractHasStock($crawler, $productDTO);
        $this->extractOverview($crawler, $productDTO);
        $this->extractFeatures($crawler, $productDTO);
        $this->extractImages($crawler, $productDTO);
    }


    private function extractName(Crawler $crawler, ProductDTO $productDTO) {
        $name = $crawler
            ->filterXPath("//h1[@id='product-title']")->text();

        $productDTO->setName($name);

    }

    private function extractOverview(Crawler $crawler, ProductDTO $productDTO) {
        $overview = $crawler
            ->filterXPath("//div[@id='product-lg-overview']/p[1]")->html();

        $productDTO->setOverview($overview);

    }

    private function extractHasStock(Crawler $crawler, ProductDTO $productDTO) {
        $stockCrawler = $crawler
            ->filterXPath("//section[@class='product-section']//div[@class='row']/div[contains(@class, 'product-info')]/div[@id='sticky-point']/form[@method='POST']");

        $hasStock = $stockCrawler->count() > 0;

        $productDTO->setHasStock($hasStock);
    }

    private function extractPrices(Crawler $crawler, ProductDTO $productDTO) {

        $priceCrawler = $crawler
            ->filterXPath("//section[@class='product-section']//div[@class='row']/div[contains(@class, 'product-info')]/div[@id='sticky-point']");

        $price = $priceCrawler
            ->filterXPath("//p[@class='price']")->text();

        $pricePrevious = null;
        $pricePreviousNode = $priceCrawler->filterXPath("//p[@class='price-previous']/span[contains(@class, 'price-value')]");
        if ($pricePreviousNode->count()) {
            $pricePrevious = $pricePreviousNode->text();
        }

        $price = PriceFormatter::getFloatedPrice($price);
        $pricePrevious = empty($pricePrevious) ? $pricePrevious : PriceFormatter::getFloatedPrice($pricePrevious);

        $productDTO->setPrice($price);
        $productDTO->setPricePrevious($pricePrevious);
    }

    private function extractFeatures(Crawler $crawler, ProductDTO $productDTO) {

        $overviewCrawler = $crawler->filterXPath("//div[@id='product-lg-overview']");

        $featuresCrawler = $overviewCrawler->filterXPath("//table[contains(@class, 'table')]/tr");
        $features = $featuresCrawler->each(
            function(Crawler $node){
                $feature        = $node->filterXPath("//td[1]")->text();
                $featureValue   = $node->filterXPath("//td[2]")->text();

                return ['feature' => $feature, 'featureValue' => $featureValue];
            });

        $productDTO->setFeatures($features);
    }

    private function extractImages(Crawler $crawler, ProductDTO $productDTO) {
        // ATTR data-zoom-image
        $imageCrawler = $crawler->filterXPath("//div[contains(@class, 'product-img-container')]//img[contains(@class, 'zoom-image')]");
        $images = $imageCrawler->each(function(Crawler $node) {

            $url = $node->attr('data-zoom-image');
            $url = URLFormatter::removeQueryString($url);

            return $url;
        });

        $productDTO->setImages($images);
    }

}
