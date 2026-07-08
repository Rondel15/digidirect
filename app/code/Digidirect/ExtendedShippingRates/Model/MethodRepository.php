<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\MethodRepositoryInterface;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Method as MethodResource;
use Digidirect\ExtendedShippingRates\Model\Carrier\MethodFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class MethodRepository extends AbstractRepository implements MethodRepositoryInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MethodRepository constructor.
     * @param MethodResource $resource
     * @param MethodFactory $factory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        MethodResource $resource,
        MethodFactory $factory,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($resource, $factory);
    }

    /**
     * Load data by given identity
     *
     * @param int $id
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        /**
         * @var AbstractModel $model
         */
        $model = parent::getById($id);
        return $this->assignStoreLabel($model);
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

    /**
     * Assign store to model
     *
     * @param AbstractModel $model
     * @return AbstractModel
     */
    protected function assignStoreLabel($model)
    {
        $label = $this->getStoreLabel($model->getId());
        if ($label) {
            $model->setTitle($label);
        }
        return $model;
    }
}
