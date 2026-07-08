<?php

namespace Digidirect\Collect\Model;

use Digidirect\Collect\Api\CollectPlaceRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class StorageHandler
 * @package Digidirect\Collect\Model
 */
class StorageHandler
{
    /**
     * StorageFactory
     *
     * @var \Digidirect\Collect\Model\StorageFactory
     */
    protected $_storageFactory;

    /**
     * StorageConfig
     *
     * @var Config\Data
     */
    protected $_storageConfig;

    /**
     * StorageHelper
     *
     * @var \Digidirect\Collect\Helper\Storage\Data
     */
    protected $_storageHelper;

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * StorageHandler constructor.
     *
     * @param \Digidirect\Collect\Model\StorageFactory $storageFactory
     * @param \Digidirect\Collect\Model\Config\Data $storageConfig
     * @param \Digidirect\Collect\Helper\Storage\Data $storageHelper
     * @param ManagerInterface|null $eventManager
     */
    public function __construct(
        \Digidirect\Collect\Model\StorageFactory $storageFactory,
        \Digidirect\Collect\Model\Config\Data $storageConfig,
        \Digidirect\Collect\Helper\Storage\Data $storageHelper,
        ManagerInterface $eventManager = null
    ) {
        $this->_storageFactory = $storageFactory;
        $this->_storageConfig = $storageConfig;
        $this->_storageHelper = $storageHelper;
        $objectManager = ObjectManager::getInstance();
        $this->eventManager = $eventManager ?: $objectManager->get(ManagerInterface::class);
    }

    /**
     * GetPlacesByPostcode:
     * 1) request to google api to get coordinates by postcode
     * 2) get all places from all storages
     * 3) check distance between postcode coordinates and place coordinates in cycle
     * 4) return all places with distance < $distance
     *
     * @param array $skus
     * @param string $postcode
     * @param int $distance
     * @param int $qty
     * @return []
     */
    public function getPlacesByData($skus, $postcode, $distance, $qty = 1)
    {
        $centralCoord = $this->_storageHelper->getCoordinatesByPostcode($postcode);
        if (!$centralCoord) {
            return ['catch' => false];
        }
        $result = ['postcode_coordinates' => $centralCoord];
        $places = $this->getPlacesBySkus($skus, $qty);
        $data = [];
        $catch = (!empty($places) ? true : false);
        if (!empty($places)) {
            //check distance first
            $data = $this->_filterPlacesByDistance($places, $centralCoord, $distance);

            //if not found - find in next range and propose
            if (empty($data)) {
                $catch = false;
                $distanceRange = explode(',', $this->_storageHelper->getDefaultDistanceRange());
                if (is_array($distanceRange)) {
                    asort($distanceRange);
                    foreach ($distanceRange as $distanceData) {
                        if ($distanceData > $distance) {
                            if (!empty($data)) {
                                break;
                            }

                            $distance = $distanceData;
                            $data = $this->_filterPlacesByDistance($places, $centralCoord, $distance);
                        }
                    }
                }
            }
        }
        $result['distance'] = $distance;
        $result['data'] = $data;
        $result['catch'] = $catch;

        $transportObject = new DataObject($result);
        $this->eventManager->dispatch(
            'digidirect_collect_after_get_places_by_data_from_storage',
            ['transportObject' => $transportObject]
        );

        return $transportObject->getData();
    }

    /**
     * FilterPlacesByDistance
     *
     * @param [] $places
     * @param [] $centralCoord
     * @param string $distance
     * @return []
     */
    protected function _filterPlacesByDistance($places, $centralCoord, $distance)
    {
        $collectPlaceResult = [];
        $i = 0;
        $showUnavailable = $this->_storageHelper->showUnavailablePlaces();
        foreach ($places as $storageName => $collectPlaces) {
            foreach ($collectPlaces as $collectPlace) {
                if (!$collectPlace->getLatitude() || !$collectPlace->getLongitude()) {
                    continue;
                }

                $unavailable = (bool)$collectPlace->getData(CollectPlaceRepositoryInterface::KEY_IS_UNAVAILABLE);
                if ($unavailable && !$showUnavailable) {
                    continue;
                }

                /** @var $collectPlace \Digidirect\Collect\Api\Data\CollectPlaceInterface*/
                $centralDistance = $this->_storageHelper->getDistanceBetweenCoordinates(
                    $centralCoord['lat'],
                    $centralCoord['long'],
                    (float)$collectPlace->getLatitude(),
                    (float)$collectPlace->getLongitude()
                );

                if ($centralDistance <= $distance) {
                    $collectPlaceResult[$i]['collectplace'] = $collectPlace;
                    $collectPlaceResult[$i]['distance'] = $centralDistance;
                    $collectPlaceResult[$i]['collectplace_storage_name'] = $storageName;
                    $collectPlaceResult[$i]['available'] = !$unavailable;
                    $collectPlaceResult[$i]['show_availability'] = $showUnavailable;
                }
                $i++;
            }
        }

        return $this->sortByDistance($collectPlaceResult);
    }

    /**
     * SortByDistance
     *
     * @param [] $data
     * @return []
     */
    protected function sortByDistance($data)
    {
        usort($data, function ($item1, $item2) {
            if ($item1['distance'] == $item2['distance']) {
                return 0;
            }

            return ($item1['distance'] < $item2['distance']) ? -1 : 1;
        });

        return $data;
    }

    /**
     * Get all storages from XML, create instances, return all places from storages by SKUs
     *
     * @param string|array $skus
     * @param int $qty
     * @return []
     * @throws \Exception
     */
    public function getPlacesBySkus($skus, $qty = 1)
    {
        $rawStorages = $this->_storageConfig->getStorageForHandle();

        $places = [];
        if (!empty($rawStorages)) {
            foreach ($rawStorages as $key => $rawStorage) {
                if (isset($rawStorage['instance'])) {
                    /** @var $storage \Digidirect\Collect\Api\Data\CollectPlaceInterface */
                    $storage = $this->_storageFactory->create($rawStorage['instance']);
                    /** @var $storage \Digidirect\Collect\Api\CollectPlaceRepositoryInterface*/
                    $skus = is_array($skus) ? $skus : array_map('trim', explode(',', $skus));
                    if (method_exists($storage, 'getListBySkus')) {
                        $allPlaces = $storage->getListBySkus($skus, $qty);
                    } else {
                        $allPlaces = $placesBySku = [];
                        foreach ($skus as $sku) {
                            $skuQty = is_array($qty) ? $qty[$sku] : $qty;
                            $skuPlaces = $placesBySku[$sku] = $storage->getListBySku($sku, $skuQty);
                            $allPlaces = array_merge($allPlaces, $skuPlaces);
                        }
                        if (method_exists($storage, 'mergePlacesBySku')) {
                            $allPlaces = $storage->mergePlacesBySku($placesBySku);
                        }
                    }

                    if (!empty($allPlaces)) {
                        $places[$key] = $allPlaces;
                    }
                }
            }
        }

        return $places;
    }

    /**
     * @param string $collectPlaceId
     * @param string $storageName
     * @return bool|\Digidirect\Collect\Api\Data\CollectPlaceInterface
     * @throws \Exception
     */
    public function getCollectPlaceById($collectPlaceId, $storageName)
    {
        $storageConfig = $this->_storageConfig->getStorageForHandle($storageName);
        if ($storageConfig && isset($storageConfig['instance'])) {
            $storage = $this->_storageFactory->create($storageConfig['instance']);
            if ($storage instanceof \Digidirect\Collect\Api\CollectPlaceRepositoryInterface) {
                return $storage->getById($collectPlaceId);
            }
        }

        return false;
    }
}
