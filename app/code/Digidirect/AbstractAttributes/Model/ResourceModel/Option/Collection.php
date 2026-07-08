<?php
namespace Digidirect\AbstractAttributes\Model\ResourceModel\Option;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Model\ResourceModel\AbstractCollection;
use Digidirect\AbstractAttributes\Helper\Attribute;
use Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Digidirect\AbstractAttributes\Model\ResourceModel\Option
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = OptionInterface::ID;

    /**
     * @var string
     */
    protected $_idEntityKey = OptionInterface::OPTION_ID;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    protected $_store;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Api\StoreRepositoryInterface $store
     * @param string $eventPrefix
     * @param string $eventObject
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Api\StoreRepositoryInterface $store,
        $eventPrefix = 'digidirect_aa_option_collection',
        $eventObject = 'option_collection',
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_store = $store;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;

        $this->addFilterToMap('option_id', 'ao.option_id');
        $this->addFilterToMap('position', 'ao.sort_order');
        $this->addFilterToMap('attribute_id', 'aa.attribute_id');
        $this->addFilterToMap('label_0', 'av.value');
        $this->addFilterToMap('status', 'main_table.status');
        $this->addFilterToMap('row_id', 'main_table.row_id');
        $this->addFilterToMap('store_id', 'main_table.store_id');
        $this->addFilterToMap('url_key', 'main_table.url_key');
    }

    /**
     * Add attribute id filter
     * @param int $attributeId
     * @return $this
     */
    public function addAttributeFilter($attributeId)
    {
        return $this->addFilter('aa.attribute_id', $attributeId);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AbstractAttributes\Model\Option', 'Digidirect\AbstractAttributes\Model\ResourceModel\Option');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->reset(\Zend_Db_Select::COLUMNS)
            ->columns()
            ->joinLeft(
                ['av' => $this->getTable('eav_attribute_option_value')],
                'main_table.option_id = av.option_id',
                ['label_0' => 'value', 'option_id']
            )
            ->joinLeft(
                ['ao' => $this->getTable('eav_attribute_option')],
                'av.option_id = ao.option_id',
                ['attribute_id', 'position' => 'sort_order']
            )->joinLeft(
                ['a' => $this->getTable('eav_attribute')],
                'ao.attribute_id = a.attribute_id',
                [
                    'attribute_label' => 'frontend_label',
                    'is_default'      => new \Zend_Db_Expr('IF(FIND_IN_SET(av.option_id, default_value) > 0, 1, 0)'),
                    'attribute_code',
                    'attribute_id'
                ]
            )->join(
                ['aa' => $this->getTable('digidirect_aa')],
                'aa.attribute_id = a.attribute_id',
                []
            )
            ->where('aa.store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            ->where('av.store_id = ?', \Magento\Store\Model\Store::DEFAULT_STORE_ID)
            ->where('aa.status = ?', Attribute::STATUS_ENABLED);

        $stores = $this->_store->getList();
        foreach ($stores as $store) {
            if ($storeId = $store->getId()) {
                $this->getSelect()->joinLeft(
                    ["av$storeId" => $this->getTable('eav_attribute_option_value')],
                    "av.option_id = av$storeId.option_id AND av$storeId.store_id = $storeId",
                    ['label_' . $storeId => 'value']
                );
            }
        }

        return $this;
    }
}
