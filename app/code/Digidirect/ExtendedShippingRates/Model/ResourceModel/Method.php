<?php
namespace Digidirect\ExtendedShippingRates\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Digidirect\ExtendedShippingRates\Model\Carrier as CarrierModel;
use Digidirect\ExtendedShippingRates\Api\Data\MethodInterface;

class Method extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\CollectionFactory
     */
    protected $rateCollectionFactory;

    /**
     * @var \Digidirect\ExtendedShippingRates\Model\Rate\Import
     */
    protected $import;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\CollectionFactory $rateCollectionFactory
     * @param \Digidirect\ExtendedShippingRates\Model\Rate\Import $import
     * @param \Psr\Log\LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\CollectionFactory $rateCollectionFactory,
        \Digidirect\ExtendedShippingRates\Model\Rate\Import $import,
        \Psr\Log\LoggerInterface $logger,
        $connectionName = null
    ) {
        $this->import = $import;
        $this->logger = $logger;
        $this->string = $string;
        $this->rateCollectionFactory = $rateCollectionFactory;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::METHOD_TABLE_NAME, MethodInterface::ENTITY_ID);
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
        $this->addRates($object);

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
     * Save method's associated store labels.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }
        $this->importRates($object);

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
     * @param int $methodId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($methodId, $labels)
    {
        $deleteByStoreIds = [];
        $table = $this->getTable(\Digidirect\ExtendedShippingRates\Model\Carrier::METHOD_LABELS_TABLE_NAME);
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $storeId => $label) {
            if ($this->string->strlen($label)) {
                $data[] = ['method_id' => $methodId, 'store_id' => $storeId, 'label' => $label];
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
                $connection->delete($table, ['method_id=?' => $methodId, 'store_id IN (?)' => $deleteByStoreIds]);
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
     * @param int $methodId
     * @return array
     */
    public function getStoreLabels($methodId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(\Digidirect\ExtendedShippingRates\Model\Carrier::METHOD_LABELS_TABLE_NAME),
            ['store_id', 'label']
        )->where(
            'method_id = :method_id'
        );
        return $this->getConnection()->fetchPairs($select, [':method_id' => $methodId]);
    }

    /**
     * Get carrier label by specific store id
     *
     * @param int $methodId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($methodId, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(\Digidirect\ExtendedShippingRates\Model\Carrier::METHOD_LABELS_TABLE_NAME),
            'label'
        )->where(
            'method_id = :method_id'
        )->where(
            'store_id IN(0, :store_id)'
        )->order(
            'store_id DESC'
        );
        return $this->getConnection()->fetchOne($select, [':method_id' => $methodId, ':store_id' => $storeId]);
    }

    /**
     * Adds corresponding shipping rates to the method
     * @param AbstractModel $object
     * @return void
     */
    public function addRates(AbstractModel $object)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Collection $rates */
        $rates = $this->getRatesCollection($object);
        $object->setRates($rates->getItems());
    }

    /**
     * @param AbstractModel $object
     * @return Rate\Collection
     */
    public function getRatesCollection(AbstractModel $object)
    {
        /** @var \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rate\Collection $rates */
        $rates = $this->rateCollectionFactory->create();
        $rates->addFieldToFilter('method_id', $object->getId());
        $rates->addOrder('priority');
        $object->setRatesCollection($rates);

        return $rates;
    }

    /**
     * @param AbstractModel $method
     * @return \Digidirect\ExtendedShippingRates\Model\Rate\Import
     */
    public function getImport(AbstractModel $method)
    {
        if ($method->getId()) {
            $this->import->setMethodId($method->getId());
        }
        return $this->import;
    }

    /**
     * Import rates
     *
     * @param AbstractModel $method
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function importRates(AbstractModel $method)
    {
        if ($method->getData('import_rates') !== null) {
            $import = $this->getImport($method);
            try {
                foreach ($method->getData('import_rates') as $rateFile) {
                    $import->processImport($rateFile);
                }
                $import->removeCsvFiles($method->getData('import_rates'));
            } catch (\Exception $e) {
                $import->removeCsvFiles($method->getData('import_rates'));
                $this->logger->error($e->getMessage());
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while importing the rate(s). For details look into "support_report.log".')
                );
            }
        }
    }
}
