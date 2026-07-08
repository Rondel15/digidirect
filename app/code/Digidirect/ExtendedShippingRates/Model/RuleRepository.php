<?php
namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Api\RuleRepositoryInterface;
use Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule as RuleResource;
use Digidirect\ExtendedShippingRates\Model\RuleFactory;
use Magento\Framework\Model\AbstractModel;

class RuleRepository extends AbstractRepository implements RuleRepositoryInterface
{
    /**
     * RuleRepository constructor.
     * @param RuleResource $resource
     * @param RuleFactory $factory
     */
    public function __construct(
        RuleResource $resource,
        RuleFactory $factory
    ) {
        parent::__construct($resource, $factory);
    }

    /**
     * Save data
     *
     * @param AbstractModel $model
     * @return AbstractModel
     * @throws CouldNotSaveException
     */
    public function save(AbstractModel $model)
    {
        /**
         * Prepare store Ids if applicable and if they were set as string in comma separated format.
         * Backwards compatibility.
         */
        if ($model->hasStoreIds()) {
            $storeIds = $model->getStoreIds();
            if (!empty($storeIds)) {
                $model->setStoreIds($storeIds);
            }
        }
        return parent::save($model);
    }
}
