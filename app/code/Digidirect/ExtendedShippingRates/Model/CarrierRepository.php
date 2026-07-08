<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\CarrierRepositoryInterface;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Carrier as CarrierResource;
use Magento\Store\Model\StoreManagerInterface;

class CarrierRepository extends AbstractRepository implements CarrierRepositoryInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CarrierRepository constructor.
     * @param CarrierResource $resource
     * @param CarrierFactory $factory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CarrierResource $resource,
        CarrierFactory $factory,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($resource, $factory);
    }

    /**
     * Get Carrier label by specified store
     *
     * @param int $id
     * @return string|bool
     */
    protected function getStoreLabel($id)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $labels = (array)$this->getStoreLabels($id);

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (!empty($labels[0])) {
            return $labels[0];
        }

        return false;
    }

    /**
     * Set if not yet and retrieve carrier store labels
     *
     * @param int $id
     * @return array
     */
    protected function getStoreLabels($id)
    {
        return $this->resource->getStoreLabels($id);
    }
}
