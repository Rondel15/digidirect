<?php

namespace Digidirect\StoreLocator\Model;

use Digidirect\Locator\Model\CoordinatesGetterInterface;
use Digidirect\StoreLocator\Helper\Config;
use Digidirect\StoreLocator\Model\Config\Source\Api;

/**
 * This class tries to find out coordinates getter depending on api to be used
 * For example if badoo/glonass coordinates are different from google GPS api
 * it's better to have their own api to get coordinates
 * @since 1.4.0
 */
class CoordinatesProviderFactory
{
    /**
     *
     * [
     *     'apiCode' => 'apiClassObject'
     * ]
     * @var array
     */
    protected $coordinatesApiConfiguration = [];

    /**
     * @var Api
     */
    protected $apiSource;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * CoordinatesProviderFactory constructor.
     * @param Api $api
     * @param Config $config
     * @param array $coordinatesApiConfiguration
     */
    public function __construct(Api $api, Config $config, array $coordinatesApiConfiguration)
    {
        $this->coordinatesApiConfiguration = $coordinatesApiConfiguration;
        $this->configHelper = $config;
        $this->apiSource = $api;
    }

    /**
     * @param null $storeId
     * @return CoordinatesGetterInterface
     */
    public function getCoordinatesProvider($storeId = null): CoordinatesGetterInterface
    {
        $usedApi = $this->configHelper->getApi($storeId);
        return $this->coordinatesApiConfiguration[$usedApi];
    }

    /**
     * @param $address
     * @return array|bool
     */
    public function getCoordinates(array $address = [])
    {
        $storeId = $address['store_id'] ?? null;
        return $this->getCoordinatesProvider($storeId)->getCoordinatesByAddress($address);
    }
}
