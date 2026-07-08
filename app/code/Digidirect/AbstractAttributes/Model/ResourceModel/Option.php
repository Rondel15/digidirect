<?php
namespace Digidirect\AbstractAttributes\Model\ResourceModel;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;

/**
 * Class Option
 * @package Digidirect\AbstractAttributes\Model\ResourceModel
 */
class Option extends AbstractDb
{
    /**
     * Get attribute id
     * @param int $optionId
     * @return int
     */
    public function getAttributeId($optionId)
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getTable('eav_attribute_option'), [OptionInterface::ATTRIBUTE_ID])
            ->where(OptionInterface::OPTION_ID . ' = ?', $optionId);
        return (int)$conn->fetchOne($select);
    }

    /**
     * Whether option is available
     * @param int $attributeId
     * @return bool
     */
    public function isOptionAvailable($attributeId)
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getTable('digidirect_aa'), [OptionInterface::ATTRIBUTE_ID])
            ->where(OptionInterface::ATTRIBUTE_ID . ' = ?', $attributeId);
        return (bool)$conn->fetchOne($select);
    }

    /**
     * Check is Admin Label unique
     * @param OptionInterface $option
     * @return bool
     */
    public function isAdminLabelUnique(OptionInterface $option)
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from(['option' => $this->getTable('eav_attribute_option')])
            ->join(
                ['value' => $this->getTable('eav_attribute_option_value')],
                new \Zend_Db_Expr('option.option_id = value.option_id')
            )
            ->where('option.attribute_id = ?', $option->getAttributeId())
            ->where('value.store_id = ?', Store::DEFAULT_STORE_ID)
            ->where('value.value LIKE ?', $option->getDefaultLabel());

        if ($option->getOptionId()) {
            $select->where('option.option_id != ?', $option->getOptionId());
        }

        return $conn->fetchOne($select) === false;
    }

    /**
     * Get Option Store Labels
     * @param int $optionId
     * @return array
     */
    public function getStoreLabelsByOptionId($optionId)
    {
        $connection = $this->getConnection();
        $bind = [':option_id' => $optionId];
        $select = $connection->select()->from(
            $this->getTable('eav_attribute_option_value'),
            ['store_id', 'value']
        )->where(
            'option_id = :option_id'
        );

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Get attribute code
     * @param int $attributeId
     * @return string
     */
    public function getAttributeCode($attributeId)
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getTable('eav_attribute'), ['attribute_code'])
            ->where(OptionInterface::ATTRIBUTE_ID . ' = ?', $attributeId);
        return $conn->fetchOne($select);
    }

    /**
     * {@inheritdoc}
     */
    protected function isObjectNotNew(\Magento\Framework\Model\AbstractModel $object)
    {
        return !$this->_useIsObjectNew || !$object->isObjectNew();
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_aa_options', OptionInterface::ID);
        $this->_useIsObjectNew = true;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->processUrlRewrites();
        return parent::_afterSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $attrCode = $this->getAttributeCode(
            $this->getAttributeId($object->getOptionId())
        );

        $object->deleteAttributeOption($attrCode);
        $object->deleteUrlRewrites();

        return parent::_beforeDelete($object);
    }

    /**
     * Perform actions after object load
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject|OptionInterface $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $conn = $this->getConnection();
        $select = $conn->select()
            ->from($this->getTable('eav_attribute_option_value'))
            ->where('option_id = ?', $object->getOptionId());

        $values = $conn->fetchAll($select);
        foreach ($values as $value) {
            $object->setData('label_' . $value['store_id'], $value['value']);
        }

        return $this;
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Framework\Model\AbstractModel|OptionInterface $object
     * @return \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), Store::DEFAULT_STORE_ID];
            $select->where('store_id in (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }
        return $select;
    }

    /**
     * @param array $options
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertMultipleOptions(array $options)
    {
        return $this->getConnection()->insertMultiple($this->getMainTable(), $options);
    }
}
