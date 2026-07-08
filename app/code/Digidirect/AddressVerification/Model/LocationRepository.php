<?php
namespace Digidirect\AddressVerification\Model;

use Digidirect\AddressVerification\Api\LocationRepositoryInterface;

/**
 * Class ImportReportRepository
 * @package Digidirect\AddressVerification\Model
 */
class LocationRepository implements LocationRepositoryInterface
{
    /**
     * @var ResourceModel\Location
     */
    protected $resourceModel;

    /**
     * @var ResourceModel\Location\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * LocationRepository constructor.
     * @param ResourceModel\Location $resourceModel
     * @param ResourceModel\Location\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Digidirect\AddressVerification\Model\ResourceModel\Location $resourceModel,
        \Digidirect\AddressVerification\Model\ResourceModel\Location\CollectionFactory $collectionFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param array $data
     * @param string $countryCode
     * @param int|null $storeId
     * @param int|null $websiteId
     * @return mixed
     */
    public function saveData(array $data, $countryCode, $storeId, $websiteId)
    {
        return $this->resourceModel->saveData($data, $countryCode, $storeId, $websiteId);
    }

    /**
     * @param string $countryCode
     * @param int|null $postcode
     * @param int|null $suburb
     * @return \Digidirect\AddressVerification\Model\ResourceModel\Location\Collection
     */
    public function findLocation($countryCode, $postcode = null, $suburb = null)
    {
        /** @var \Digidirect\AddressVerification\Model\ResourceModel\Location\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('country_code', $countryCode);
        if ($postcode !== null) {
            $collection->addFieldToFilter('postcode', ['like' => $postcode . '%']);
        }
        if ($suburb !== null) {
            $collection->addFieldToFilter('suburb', ['like' => $suburb . '%']);
        }
        return $collection;
    }

    /**
     * @param string $path
     * @return array
     */
    public function loadFileConfig($path)
    {
        return $this->resourceModel->loadAupostConfig($path);
    }
}
