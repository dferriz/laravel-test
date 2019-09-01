<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Storer;

use App\DataTransferObject\ProductDTO;
use App\Entity\Feature;
use App\Entity\FeatureValue;
use App\Entity\Product;
use App\Entity\ProductFeature;
use App\Entity\ProductImage;
use App\Util\File;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class ProductStorer
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   2/08/19 13:37
 * @package App\Console\Service\DataImporter\Crawler\Storer
 */
final class ProductStorer implements ProductStorerInterface
{
    private $entityManager;
    private $style;
    private $logger;
    private $productImageRootDir;
    private $filesystem;

    /**
     * ProductStorer constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param StyleInterface         $style
     * @param LoggerInterface        $logger
     * @param Filesystem             $filesystem
     * @param string                 $productImageRootDir
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        StyleInterface $style,
        LoggerInterface $logger,
        Filesystem $filesystem,
        $productImageRootDir
    ) {
        $this->entityManager    = $entityManager;
        $this->style            = $style;
        $this->logger           = $logger;
        $this->filesystem       = $filesystem;
        $this->productImageRootDir = $productImageRootDir;
    }

    /**
     * @param array $productsDTO
     */
    public function store(array $productsDTO)
    {
        $this->style->section('Store <info>PRODUCT</info> entities');

        $productRepository = $this->entityManager->getRepository(Product::class);

        foreach($productsDTO as $productDTO) {
            /** @var ProductDTO $productDTO */
            $productId = $productDTO->getId();

            $isNew = false;
            $product = $productRepository->find($productId);
            if (!$product instanceof Product) {
                $isNew = true;
                $product = new Product($productId);
            }
            $newProductMsg = "Storing product %s";
            $updProductMsg = "Updating product %s";
            $this->style->text( vsprintf( ($isNew ? $newProductMsg : $updProductMsg), [$productId] ) );

            try {
                $product->setName($productDTO->getName());
                $product->setOverview($productDTO->getOverview());
                $product->setPrice($productDTO->getPrice());
                $product->setPricePrevious($productDTO->getPricePrevious());
                $product->setHasStock($productDTO->hasStock());
                $product->setIsChecked(true);
                $this->entityManager->persist($product);
                $this->entityManager->flush();

                $this->storeFeatures($product, $productDTO);
                $this->storeImages($product, $productDTO);

            } catch( \Exception $e) {
                $this->style->error(vsprintf("Cannot store product %s. Message error: %s", [$product->getId(), $e->getMessage()]));
                $this->entityManager->flush();
                $this->entityManager->clear();
                continue;
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->style->text(vsprintf("<info>Ok</info>",[]));
        }
    }

    /**
     * @param Product    $product
     * @param ProductDTO $productDTO
     */
    private function storeFeatures(Product $product, ProductDTO $productDTO) {

        $featureRepository          = $this->entityManager->getRepository(Feature::class);
        $featureValueRepository     = $this->entityManager->getRepository(FeatureValue::class);
        $productFeatureRepository   = $this->entityManager->getRepository(ProductFeature::class);

        $features = $productDTO->getFeatures();

        foreach($features as $item) {
            $featureName        = $item['feature'];
            $featureValueValue  = $item['featureValue'];

            $feature = $featureRepository->findOneBy(['name'=>$featureName]);
            if ( !$feature instanceof Feature ) {
                $feature = new Feature();
                $feature->setName($featureName);
                $this->entityManager->persist($feature);
            }

            $featureValue = $featureValueRepository->findOneBy(['feature'=>$feature->getId(), 'value'=>$featureValueValue]);
            if ( !$featureValue instanceof FeatureValue ) {
                $featureValue = new FeatureValue();
                $featureValue->setValue($featureValueValue);
                $featureValue->setFeature($feature);
                $this->entityManager->persist($featureValue);
            }

            $productFeature = $productFeatureRepository->findOneBy(['product'=>$product->getId(), 'feature'=>$feature->getId(), 'featureValue'=>$featureValue->getId()]);
            if ( !$productFeature instanceof ProductFeature ) {
                $productFeature = new ProductFeature();
                $productFeature->setProduct($product);
                $productFeature->setFeature($feature);
                $productFeature->setFeatureValue($featureValue);
                $this->entityManager->persist($productFeature);
            }
        }

    }

    /**
     * @param Product    $product
     * @param ProductDTO $productDTO
     */
    private function storeImages(Product $product, ProductDTO $productDTO) {

        $productImageRepository = $this->entityManager->getRepository(ProductImage::class);

        $images = $productDTO->getImages();
        foreach($images as $imageUrl) {

            $uuid5 = Uuid::uuid5(Uuid::NAMESPACE_DNS, $imageUrl);
            $uuid = $uuid5->toString();

            $productImage = $productImageRepository->findOneBy(['uuid'=>$uuid]);
            if ( !$productImage instanceof ProductImage) {

                $finfo      = new \finfo(FILEINFO_MIME_TYPE);
                $image      = file_get_contents($imageUrl);
                $mimeType   = $finfo->buffer($image);
                $extension  = File::getExtensionFromMimeType($mimeType);

                $fileName = $uuid.'.'.$extension;

                $productImage = new ProductImage();
                $productImage->setUuid($uuid);
                $productImage->setIsCover(false);
                $productImage->setProduct($product);
                $productImage->setFileName($fileName);

                $dirPath = $this->productImageRootDir.'/'.implode('/', str_split(strval($product->getId())));
                if (!$this->filesystem->exists($dirPath)) {
                    $this->filesystem->mkdir($dirPath);
                }

                $imagePath = $dirPath.'/'.$fileName;
                if ( !file_put_contents($imagePath , $image) ) {
                    $message = vsprintf("Can't save product %s image %s", [$product->getId(), $imagePath]);
                    $this->style->error($message);
                    $this->logger->warning($message);
                    continue;
                }

                $this->entityManager->persist($productImage);

            }
        }
    }


}
