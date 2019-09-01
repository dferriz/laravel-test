<?php
declare(strict_types=1);

namespace App\Console\Services\DataImporter\Crawler\Client;

use App\Console\Services\DataImporter\Base\Cache\EndpointCacheInterface;
use App\Util\Timer;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;

/**
 * Class Client
 *
 * @author  David Ferriz Candela <dev@dferriz.es>
 * @since   31/07/19 10:13
 * @package App\Console\Service\DataImporter\Crawler\Client
 */
final class Client implements ClientInterface
{
    // Activate or deactivates the cache, activate it only for dev purposes.
    private $activeCache;
    protected $cache;
    protected $client;

    protected $endpointMinS = 0.5;
    protected $endpointWaitMicro = 250000;

    public function __construct(
        EndpointCacheInterface $cache,
        GuzzleClientInterface $client,
        float $endpointMinS,
        int $endpointWaitMicro,
        bool $activeCache = false
    ) {
        $this->cache = $cache;
        $this->client = $client;
        $this->endpointMinS = $endpointMinS;
        $this->endpointWaitMicro = $endpointWaitMicro;
        $this->activeCache = $activeCache;
    }

    /**
     * @param $endpoint
     * @param $params
     *
     * @return \Exception|ClientException|RequestException|ServerException|string
     * @throws GuzzleException
     */
    public function get($endpoint, $params = [])
    {

        if ($this->activeCache){

            if ( $this->cache->isCached($endpoint, $params) ) {
                $data = $this->cache->retrieveCache($endpoint, $params);
                return $data;
            }
        }

        $this->sleepIfProceed();

        try
        {
            $result = $this->client->request('GET', $endpoint, ['query' => $params]);
            $data = $result->getBody()->getContents();

        } catch( ClientException $e) {
            $this->sleepIfProceed();
            return $e;
        } catch( ServerException $e) {
            $this->sleepIfProceed();
            return $e;
        } catch( RequestException $e) {
            $this->sleepIfProceed();
            return $e;
        }

        if ($this->activeCache) {
            $this->cache->storeCache($endpoint, $params, $data);
        }

        return $data;
    }


    private function sleepIfProceed() :void {

        if (!Timer::getInstance()->isStarted('call_endpoint')){
            Timer::getInstance()->start('call_endpoint');
            return;
        }

        Timer::getInstance()->end('call_endpoint');
        $results = Timer::getInstance()->results();

        if ( $results['call_endpoint'] < $this->endpointMinS ) {
            usleep($this->endpointWaitMicro);
        }
    }

}
