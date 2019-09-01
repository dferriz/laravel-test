<?php
declare(strict_types=1);

namespace  App\Console\Service\DataImporter\Base\Cache;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Class RequestCache
 *
 * Cache class for dev purposes. It helps to develop without making constant calls to the endpoint.
 *
 * @author David Ferriz Candela <dev@dferriz.es>
 * @since  31/07/19 11:58
 */
final class EndpointCache implements EndpointCacheInterface
{
    const SPLIT_LEN = 2;
    private $cacheDir;
    private $fileSystem;

    public function __construct($config) {

        $this->fileSystem = new Filesystem();
        $this->loadConfig($config);
    }

    private function loadConfig($config) {
        $this->cacheDir = $config['endpoint_cache_dir'];
    }


    /**
     * Checks if endpoing query is stored.
     * @param $endpoint
     * @param $params
     *
     * @return bool
     */
    public function isCached($endpoint, $params): bool {

        $fileName = static::generateFileName($endpoint, $params);
        $fileDir = $this->cacheDir.'/'.implode('/',str_split($fileName, static::SPLIT_LEN));
        $cachedFileName = $fileDir.'/'.$fileName;

        if (!$this->fileSystem->exists($fileDir)) {
            $this->fileSystem->mkdir($fileDir);
        }


        $oldFile = $this->cacheDir.'/'.$fileName;
        if ($this->fileSystem->exists($oldFile) ){
            $this->fileSystem->rename($oldFile, $cachedFileName);
        }


        if ($this->fileSystem->exists($cachedFileName)) {
            return true;
        }

        return false;
    }

    /**
     * Store data from an endpoint query in an file.
     * @param $endpoint
     * @param $params
     * @param $data
     */
    public function storeCache($endpoint, $params, $data) :void {

        $fileName = static::generateFileName($endpoint, $params);
        $fileDir = $this->cacheDir.'/'.implode('/', str_split($fileName, static::SPLIT_LEN));

        $cachedFileName = $fileDir.'/'.$fileName;

        $this->fileSystem->dumpFile($cachedFileName, $data);
    }

    public function retrieveCache($endpoint, $params) :string {

        $fileName = static::generateFileName($endpoint, $params);
        $fileDir = $this->cacheDir.'/'.implode('/', str_split($fileName, static::SPLIT_LEN));

        $cachedFileName = $fileDir.'/'.$fileName;

        $data = file_get_contents($cachedFileName);

        return $data;
    }

    /**
     * Creates an unique md5 based filename using endpoint plus querystring params.
     *
     * @param $endpoint
     * @param $params
     *
     * @return string
     */
    public static function generateFileName($endpoint, $params) {

        asort($params);
        $serializedParams = serialize($params);
        $key = md5($endpoint.'_'.$serializedParams);

        return $key;
    }


}