<?php
namespace Digidirect\AbstractEntity\Model\ResourceModel\Eav;

use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;

class Attribute extends \Magento\Catalog\Model\ResourceModel\Eav\Attribute
{
    const MODULE_NAME = 'Digidirect_AbstractEntity';

    const ENTITY = 'digidirect_abstractentity_eav_attribute';

    const KEY_IS_GLOBAL = 'is_global';

    const KEY_USE_IN_INDEX_TABLE = 'use_in_index_table';

    const KEY_IS_USED_IN_GRID = 'is_used_in_grid';

    const KEY_IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';

    const USE_IN_INDEX_TABLE_ENABLE = 1;

    const USE_IN_INDEX_TABLE_DISABLE = 0;

    /**
     * Event object name
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EavAttribute::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setSourceModel($model)
    {
        $model = $this->getData('source_model') ?: $model;
        return parent::setSourceModel($model);
    }

    /**
     * Resolve conflict between 'default_value' column
     * from 'eav_attribute' and 'digidirect_abstractentity_eav_attribute' tables.
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        return $this->setDefaultValue($this->loadDefaultValue());
    }

    /**
     * @return mixed
     */
    public function loadDefaultValue()
    {
        $attributeId = $this->getAttributeId();

        if ($this->_resource && $attributeId) {
            $connection = $this->_resource->getConnection();

            $select = $connection->select()
                ->from($connection->getTableName('eav_attribute'), ['default_value'])
                ->where('attribute_id = ?', $attributeId);
            $defaultValue = $connection->fetchOne($select);

            return $defaultValue ?: $this->getDefaultValue();
        }
        return $this->getDefaultValue();
    }
}
