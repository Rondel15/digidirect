<?php
namespace Digidirect\AbstractAttributes\Model\ResourceModel;

use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Digidirect\AbstractAttributes\Helper\Attribute;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;

/**
 * Class AbstractAttribute
 * @package Digidirect\AbstractAttributes\Model\ResourceModel
 */
class AbstractAttribute extends AbstractDb
{
    /**
     * @var Option\Collection
     */
    protected $optionCollectionFactory;

    /**
     * AbstractAttribute constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Option\CollectionFactory $optionCollectionFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Option\CollectionFactory $optionCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * Get status
     * @param int $id
     * @return string
     */
    public function getStatus($id)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [AbstractAttributeInterface::STATUS])
            ->where(AbstractAttributeInterface::ATTRIBUTE_ID . ' = ?', $id);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get available attributes
     * @return array
     */
    public function getAvailableAttributes()
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), [
                AbstractAttributeInterface::ATTRIBUTE_ID,
                AbstractAttributeInterface::STATUS
            ])
            ->where(AbstractAttributeInterface::STATUS . ' = ?', Attribute::STATUS_ENABLED);

        return $this->getConnection()->fetchAssoc($select);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isObjectNotNew(\Magento\Framework\Model\AbstractModel $object)
    {
        return !$this->_useIsObjectNew || !$object->isObjectNew();
    }

    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_aa', AbstractAttributeInterface::ID);
        $this->_useIsObjectNew = true;
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel|AbstractAttributeInterface $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        $select->join(
            ['eav' => $this->getTable('eav_attribute')],
            'eav.attribute_id = digidirect_aa.attribute_id',
            AbstractAttributeInterface::EAV_FIELDS
        );
        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];
            $select->where('store_id in (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }
        return $select;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel|AbstractAttributeInterface $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->processUrlRewrites();
        $this->_update($object->getAttributeId(), 'status', $object->getStatus());
        if ($object->getStatus()) {
            /** @var Option\Collection $optionCollection */
            $optionCollection = $this->optionCollectionFactory->create();
            $options = $optionCollection->addAttributeFilter($object->getAttributeId())->getItems();
            /** @var \Digidirect\AbstractAttributes\Model\Option $option */
            foreach ($options as $option) {
                $option->processUrlRewrites();
            }
        }
        return parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->deleteUrlRewrites();
        return parent::_beforeDelete($object);
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getTable('eav_attribute_label'))
            ->where('attribute_id = ?', $object->getAttributeId());

        $values = $conn->fetchAll($select);
        foreach ($values as $value) {
            $object->setData(AbstractAttributeInterface::ATTRIBUTED_LABEL . '_' . $value['store_id'], $value['value']);
        }
        return $this;
    }

    /**
     * Update
     * @param int $attributeId
     * @param string $field
     * @param bool|int|string $value
     * @return int
     */
    private function _update($attributeId, $field, $value)
    {
        $data = [
            $field => $value
        ];
        return $this->getConnection()->update(
            $this->getMainTable(),
            $data,
            $this->getConnection()->quoteInto('attribute_id = ?', $attributeId)
        );
    }
}
