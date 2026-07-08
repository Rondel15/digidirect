<?php
namespace Digidirect\ExtendedShippingRates\Model\ResourceModel;

use Digidirect\ExtendedShippingRates\Api\Data\CarrierInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier as CarrierModel;
use Magento\Framework\Model\AbstractModel;

class Carrier extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var Method\CollectionFactory
     */
    protected $methodsCollectionFactory;

    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var bool
     */
    protected $onlyActiveMethods = false;

    /**
     * Carrier constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Method\CollectionFactory $methodsCollectionFactory
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Method\CollectionFactory $methodsCollectionFactory,
        \Magento\Framework\Stdlib\StringUtils $string,
        $connectionName = null
    ) {
        $this->methodsCollectionFactory = $methodsCollectionFactory;
        $this->string = $string;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::CARRIER_TABLE_NAME, CarrierInterface::CARRIER_ID);
    }

    /**
     * Add customer group ids and store ids to rule data after load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        parent::_afterLoad($object);
        $this->addMethods($object);

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);

        return $this;
    }

    /**
     * Save carrier's associated store labels.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        parent::load($object, $value, $field);
        $object->setData('skip_resource_after_load', true);
        $object->afterLoad();
        $object->unsetData('skip_resource_after_load');

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::delete($object);
        $object->afterDelete();

        return $this;
    }

    /**
     * Save carrier labels for different store views
     *
     * @param int $carrierId
     * @param array $labels
     * @return $this
     * @throws \Exception
     */
    public function saveStoreLabels($carrierId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable(\Digidirect\ExtendedShippingRates\Model\Carrier::CARRIER_LABELS_TABLE_NAME);
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($this->string->strlen($label)) {
                $data[] = ['carrier_id' => $carrierId, 'store_id' => $storeId, 'label' => $label];
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['label']);
            }

            if (!empty($deleteByStoreIds)) {
                $connection->delete($table, ['carrier_id=?' => $carrierId, 'store_id IN (?)' => $deleteByStoreIds]);
            }
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing carrier labels
     *
     * @param int $carrierId
     * @return array
     */
    public function getStoreLabels($carrierId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(\Digidirect\ExtendedShippingRates\Model\Carrier::CARRIER_LABELS_TABLE_NAME),
            ['store_id', 'label']
        )->where(
            'carrier_id = :carrier_id'
        );

        return $this->getConnection()->fetchPairs($select, [':carrier_id' => $carrierId]);
    }

    /**
     * Get carrier label by specific store id
     *
     * @param int $carrierId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($carrierId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(\Digidirect\ExtendedShippingRates\Model\Carrier::CARRIER_LABELS_TABLE_NAME),
            'label'
        )->where(
            'carrier_id = :carrier_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );

        return $this->getConnection()->fetchOne($select, [':carrier_id' => $carrierId, ':store_id' => $storeId]);
    }

    /**
     * Adds corresponding shipping methods to the carrier
     *
     * @param AbstractModel $object
     * @return void
     */
    public function addMethods(AbstractModel $object)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Method\Collection $methods */
        $methods = $this->getMethodsCollection($object);
        $object->setMethods($methods->getItems());
    }

    /**
     * @param AbstractModel $object
     * @return Method\Collection
     */
    public function getMethodsCollection(AbstractModel $object)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Method\Collection $methods */
        $methods = $this->methodsCollectionFactory->create();
        $methods->addFieldToFilter('carrier_id', $object->getId());
        if ($this->isOnlyActiveMethodsFlag()) {
            $methods->addFieldToFilter('active', 1);
        }
        $object->setMethodsCollection($methods);

        return $methods;
    }

    /**
     * Set Only Active Methods Flag
     *
     * @return void
     */
    public function setOnlyActiveMethodsFlag()
    {
        $this->onlyActiveMethods = true;
    }

    /**
     * Get Only Active Methods Flag
     *
     * @return bool
     */
    public function isOnlyActiveMethodsFlag()
    {
        return $this->onlyActiveMethods;
    }

    /**
     * Remove Only Active Methods Flag
     *
     * @return void
     */
    public function removeOnlyActiveMethodsFlag()
    {
        $this->onlyActiveMethods = false;
    }
}
