<?php

namespace Digidirect\AbstractEntity\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AdditionalAttributes;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute;

/**
 * Upgrade the module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ModuleContextInterface
     */
    protected $_context;

    /**
     * @var SchemaSetupInterface
     */
    protected $_setup;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->_context = $context;
        $this->_setup = $setup;
        $this->_connection = $setup->getConnection();

        $this->_setup->startSetup();

        if ($this->_compareVersions('1.0.3')) {
            $this->_addParentEntityField();
            $this->_createRelationTable();
        }

        if ($this->_compareVersions('1.0.4')) {
            $this->_createAdditionalAttributesTable();
        }

        if ($this->_compareVersions('1.0.5')) {
            $this->_updateAdditionalAttributesTable();
        }

        if ($this->_compareVersions('1.0.6')) {
            $this->addSourceEntityColumn();
        }

        if ($this->_compareVersions('1.0.7')) {
            $this->_addParentEntityFieldRequired();
        }

        if ($this->_compareVersions('1.0.8')) {
            $this->_addEntityPerPageToAdditionalAttributesTable();
        }

        if ($this->_compareVersions('1.0.9')) {
            $this->_addColumnsForWysiwyg();
        }

        if ($this->_compareVersions('1.1.0')) {
            $this->_addColumnForIndexingToAEAttributes();
        }

        if ($this->_compareVersions('1.1.1')) {
            $this->_addColumnsForDisplayingInGrid();
        }

        if ($this->_compareVersions('1.1.2')) {
            $this->_modifyDecimalValueLength();
        }

        $this->_setup->endSetup();
    }

    /**
     * @return string
     */
    protected function _getEntityTable()
    {
        return $this->_setup->getTable('digidirect_abstractentity_entity');
    }

    /**
     * @return string
     */
    protected function _getRelationTable()
    {
        return $this->_setup->getTable('digidirect_abstractentity_entity_relation');
    }

    /**
     * @return string
     */
    protected function _getAdditionalAttributesTable()
    {
        return $this->_setup->getTable('digidirect_abstractentity_entity_additional_attributes');
    }

    /**
     * Add parent_id field
     *
     * @return void
     */
    protected function _addParentEntityField()
    {
        if (!$this->_isTableExists($this->_getEntityTable())) {
            return;
        }

        $parentIdField = 'parent_id';

        if (!$this->_isColumnExists($this->_getEntityTable(), $parentIdField)) {
            $this->_connection->addColumn(
                $this->_getEntityTable(),
                $parentIdField,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Parent Entity Id',
                ]
            );
        }
    }

    /**
     * Add is_parent_required field
     *
     * @return void
     */
    protected function _addParentEntityFieldRequired()
    {
        if (!$this->_isTableExists($this->_getRelationTable())) {
            return;
        }

        $parentIdFieldRequired = \Digidirect\AbstractEntity\Model\ResourceModel\Relation::IS_PARENT_REQUIRED;
        if (!$this->_isColumnExists($this->_getRelationTable(), $parentIdFieldRequired)) {
            $this->_connection->addColumn(
                $this->_getRelationTable(),
                $parentIdFieldRequired,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Parent Entity Id Requird for Entities',
                ]
            );
        }
    }

    /**
     * Create 'digidirect_abstractentity_entity_relation' table
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function _createRelationTable()
    {
        if ($this->_isTableExists($this->_getRelationTable())) {
            return;
        }

        /**
         * Create table 'digidirect_abstractentity_entity_relation'
         */
        $table = $this->_setup->getConnection()
            ->newTable($this->_getRelationTable())
            ->addColumn(
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Attribute Set ID'
            )
            ->addColumn(
                'parent_attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Parent Attribute Set ID'
            )
            ->addIndex(
                $this->_setup->getIdxName('digidirect_abstractentity_entity_link', ['parent_attribute_set_id']),
                ['parent_attribute_set_id']
            )
            ->addForeignKey(
                $this->_setup->getFkName(
                    'digidirect_abstractentity_entity_link',
                    'parent_attribute_set_id',
                    'eav_attribute_set',
                    'attribute_set_id'
                ),
                'parent_attribute_set_id',
                $this->_setup->getTable('eav_attribute_set'),
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->_setup->getFkName(
                    'digidirect_abstractentity_entity_link',
                    'attribute_set_id',
                    'eav_attribute_set',
                    'attribute_set_id'
                ),
                'attribute_set_id',
                $this->_setup->getTable('eav_attribute_set'),
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Relation of Abstract Entities');
        $this->_setup->getConnection()->createTable($table);
    }

    /**
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function _createAdditionalAttributesTable()
    {
        if ($this->_isTableExists($this->_getAdditionalAttributesTable())) {
            return;
        }

        /**
         * Create table 'digidirect_abstractentity_entity_additional_attributes'
         */
        $table = $this->_setup->getConnection()
            ->newTable($this->_getAdditionalAttributesTable())
            ->addColumn(
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Attribute Set ID'
            )
            ->addColumn(
                'url_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'primary' => false],
                'Url Key'
            )
            ->addIndex(
                $this->_setup->getIdxName('digidirect_abstractentity_entity_link', ['attribute_set_id']),
                ['attribute_set_id']
            )
            ->addForeignKey(
                $this->_setup->getFkName(
                    'digidirect_abstractentity_entity_additional_attributes',
                    'attribute_set_id',
                    'eav_attribute_set',
                    'attribute_set_id'
                ),
                'attribute_set_id',
                $this->_setup->getTable('eav_attribute_set'),
                'attribute_set_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Additional Attributes of Abstract Entities');
        $this->_setup->getConnection()->createTable($table);
    }

    /**
     * @return void
     */
    protected function _updateAdditionalAttributesTable()
    {
        $table = $this->_getAdditionalAttributesTable();
        if (!$this->_isTableExists($table)) {
            return;
        }

        $columns = [
            AdditionalAttributes::DESCRIPTION => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => false,
                'comment' => 'Entity\'s Description',
            ],
            AdditionalAttributes::VISIBLE_ON_FRONTEND => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'comment' => 'Listing Page on Frontend',
            ]

        ];

        $connection = $this->_setup->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($table, $name, $definition);
        }
    }

    /**
     * Compare versions
     *
     * @param string $new
     * @return bool
     */
    protected function _compareVersions($new)
    {
        return version_compare($this->_context->getVersion(), $new, '<');
    }

    /**
     * Check if column exists
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    protected function _isColumnExists($tableName, $columnName)
    {
        return $this->_connection->tableColumnExists($tableName, $columnName);
    }

    /**
     * Check if table exists
     *
     * @param string $tableName
     * @return bool
     */
    protected function _isTableExists($tableName)
    {
        return $this->_connection->isTableExists($tableName);
    }

    /**
     * @return $this
     */
    protected function addSourceEntityColumn()
    {
        $this->_setup->getConnection()->addColumn(
            'digidirect_abstractentity_eav_attribute',
            'source_entity_type',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'Source Entity Type',
            ]
        );
        return $this;
    }

    /**
     * @return void
     */
    protected function _addEntityPerPageToAdditionalAttributesTable()
    {
        $table = $this->_getAdditionalAttributesTable();
        if (!$this->_isTableExists($table)) {
            return;
        }

        $columns = [
            AdditionalAttributes::ENTITIES_PER_LISTING_PAGE => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'nullable' => false,
                'comment' => 'Entities Per Listing Page on Frontend',
            ]
        ];

        $connection = $this->_setup->getConnection();
        foreach ($columns as $name => $definition) {
            $connection->addColumn($table, $name, $definition);
        }
    }

    /**
     * @return $this
     */
    protected function _addColumnsForWysiwyg()
    {
        $this->_setup->getConnection()->addColumn(
            'digidirect_abstractentity_eav_attribute',
            'is_html_allowed_on_front',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'unsigned' => true,
                'comment' => 'Is HTML Allowed On Front'
            ]
        );

        $this->_setup->getConnection()->addColumn(
            'digidirect_abstractentity_eav_attribute',
            'is_wysiwyg_enabled',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'unsigned' => true,
                'comment' => 'Is WYSIWYG Enabled'
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addColumnForIndexingToAEAttributes()
    {
        $this->_setup->getConnection()->addColumn(
            'digidirect_abstractentity_eav_attribute',
            Attribute::KEY_USE_IN_INDEX_TABLE,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => Attribute::USE_IN_INDEX_TABLE_ENABLE,
                'unsigned' => true,
                'comment' => 'Is Allowed For Index Table '
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function _addColumnsForDisplayingInGrid()
    {
        $this->_setup->getConnection()->addColumn(
            'digidirect_abstractentity_eav_attribute',
            Attribute::KEY_IS_USED_IN_GRID,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'unsigned' => true,
                'comment' => 'Is Used in Grid'
            ]
        );

        $this->_setup->getConnection()->addColumn(
            'digidirect_abstractentity_eav_attribute',
            Attribute::KEY_IS_FILTERABLE_IN_GRID,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'unsigned' => true,
                'comment' => 'Is Filterable in Grid'
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function _modifyDecimalValueLength()
    {
        $this->_setup->getConnection()->modifyColumn(
            $this->_setup->getTable('digidirect_abstractentity_entity_decimal'),
            'value',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '15,10'
            ]
        );
        return $this;
    }
}
