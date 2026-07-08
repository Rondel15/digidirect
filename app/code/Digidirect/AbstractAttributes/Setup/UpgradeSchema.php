<?php

namespace Digidirect\AbstractAttributes\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

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

        if ($this->_compareVersions('0.0.2')) {
            $this->_addAdvancedOptionsFields();
        }

        if ($this->_compareVersions('0.0.3')) {
            $this->_renameAdvancedOptionsFields();
        }

        if ($this->_compareVersions('0.0.4')) {
            $this->_removeUniqueKeyFromOptions();
        }

        if ($this->_compareVersions('0.0.5')) {
            $this->_changeTypeOfDescriptionColumn();
        }

        if ($this->_compareVersions('0.0.6')) {
            $this->_addOptionsColumns();
        }

        if ($this->_compareVersions('0.0.7')) {
            $this->_addOptionsStoreIds();
        }

        if ($this->_compareVersions('0.0.8')) {
            $this->_addOptionsAutoIncrement();
        }

        if ($this->_compareVersions('0.0.9')) {
            $this->_addAttributeStoreIds();
        }

        if ($this->_compareVersions('0.0.10')) {
            $this->_removeAttributeIdUniqueKeyFromAttributes();
        }

        if ($this->_compareVersions('0.0.11')) {
            $this->_addAttributeTemplates();
        }

        if ($this->_compareVersions('0.0.12')) {
            $this->_addOptionDesignFields();
        }

        if ($this->_compareVersions('1.1.0')) {
            $this->_addOptionSortOrderField();
        }

        if ($this->_compareVersions('1.1.1')) {
            $this->addDateFields();
        }

        $this->_setup->endSetup();
    }

    /**
     * @return string
     */
    protected function getOptionsTable()
    {
        return $this->_setup->getTable('digidirect_aa_options');
    }

    /**
     * Add create and update date fields
     *
     * @return void
     */
    protected function addDateFields()
    {
        if (!$this->_isTableExists($this->getOptionsTable())) {
            return;
        }

        $createdAt = 'aa_option_created_at';
        $updatedAt = 'aa_option_updated_at';

        if (!$this->_isColumnExists($this->getOptionsTable(), $createdAt)) {
            $this->_connection->addColumn(
                $this->getOptionsTable(),
                $createdAt,
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                    'comment' => 'Creation Date',
                ]
            );
        }

        if (!$this->_isColumnExists($this->getOptionsTable(), $updatedAt)) {
            $this->_connection->addColumn(
                $this->getOptionsTable(),
                $updatedAt,
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Update Date',
                ]
            );
        }
    }

    /**
     * Add new fields to digidirect_aa table: aa_listing_enabled, aa_url_key, aa_meta_title, aa_meta_desc
     *
     * @return void
     */
    protected function _addAdvancedOptionsFields()
    {
        $tableName = 'digidirect_aa';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Is Listing Enabled" column
        $columnName = 'aa_listing_enabled';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Listing Enabled',
                ]
            );
        }

        // Add "Url Key" column
        $columnName = 'aa_url_key';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Url Key',
                ]
            );
        }

        // Add "Meta Title" column
        $columnName = 'aa_meta_title';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Meta Keywords',
                ]
            );
        }

        // Add "Meta Description" column
        $columnName = 'aa_meta_desc';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => false,
                    'comment' => 'Meta Description',
                ]
            );
        }
    }

    /**
     * Remove old fields from digidirect_aa table: aa_listing_enabled, aa_url_key, aa_meta_title, aa_meta_desc
     * Add new fields to digidirect_aa table: aa_listing_enabled, aa_url_key, aa_meta_title, aa_meta_desc
     *
     * @return void
     */
    protected function _renameAdvancedOptionsFields()
    {
        $tableName = 'digidirect_aa';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Remove old "Is Listing Enabled" column
        $columnName = 'aa_listing_enabled';
        if ($this->_isColumnExists($tableName, $columnName)) {
            $this->_deleteColumn($tableName, $columnName);
        }

        // Add "Is Listing Enabled" column
        $columnName = 'listing_enabled';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Is Listing Enabled',
                ]
            );
        }

        // Remove old "Url Key" column
        $columnName = 'aa_url_key';
        if ($this->_isColumnExists($tableName, $columnName)) {
            $this->_deleteColumn($tableName, $columnName);
        }

        // Add "Url Key" column
        $columnName = 'url_key';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Url Key',
                ]
            );
        }

        // Remove old "Meta Title" column
        $columnName = 'aa_meta_title';
        if ($this->_isColumnExists($tableName, $columnName)) {
            $this->_deleteColumn($tableName, $columnName);
        }

        // Add "Meta Title" column
        $columnName = 'meta_title';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Meta Keywords',
                ]
            );
        }

        // Remove old "Meta Description" column
        $columnName = 'aa_meta_desc';
        if ($this->_isColumnExists($tableName, $columnName)) {
            $this->_deleteColumn($tableName, $columnName);
        }

        // Add "Meta Description" column
        $columnName = 'meta_desc';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => false,
                    'comment' => 'Meta Description',
                ]
            );
        }
    }

    /**
     * Remove unique key from "url_key" column in digidirect_aa_options table
     *
     * @return void
     */
    protected function _removeUniqueKeyFromOptions()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        $this->_connection->dropIndex(
            $this->_setup->getTable($tableName),
            $this->_setup->getIdxName(
                $tableName,
                'url_key',
                AdapterInterface::INDEX_TYPE_UNIQUE
            )
        );
    }

    /**
     * Change type of "description" column from varchar to text in digidirect_aa_options table
     *
     * @return void
     */
    protected function _changeTypeOfDescriptionColumn()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        $columnName = 'description';
        if ($this->_isColumnExists($tableName, $columnName)) {
            $this->_deleteColumn($tableName, $columnName);
        }

        // Add "Meta Description" column
        $columnName = 'description';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => false,
                    'comment' => 'Option Description',
                ]
            );
        }
    }

    /**
     * Add new fields to digidirect_aa digidirect_aa_options: status, include_in_widget, widget_logo, meta_title, meta_desc
     *
     * @return void
     */
    protected function _addOptionsColumns()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Status" column
        $columnName = 'status';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Status',
                ]
            );
        }

        // Add "Include in Widget" column
        $columnName = 'include_in_widget';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Include in Widget',
                ]
            );
        }

        // Add "Widget Logo" column
        $columnName = 'widget_logo';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Widget Logo',
                ]
            );
        }

        // Add "Meta Title" column
        $columnName = 'meta_title';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Meta Title',
                ]
            );
        }

        // Add "Meta Description" column
        $columnName = 'meta_desc';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => '64k',
                    'nullable' => false,
                    'comment' => 'Meta Description',
                ]
            );
        }
    }

    /**
     * Add store_id to digidirect_aa_options
     *
     * @return void
     */
    protected function _addOptionsStoreIds()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Store ID" column
        $columnName = 'store_id';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Store ID',
                ]
            );
        }

        $this->_connection->addIndex(
            $this->_setup->getTable($tableName),
            $this->_setup->getIdxName(
                $tableName,
                ['option_id', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['option_id', 'store_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * Add auto increment column to digidirect_aa_options
     *
     * @return void
     */
    protected function _addOptionsAutoIncrement()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Row ID" column
        $columnName = 'row_id';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'auto_increment' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment' => 'Row ID',
                ]
            );
        }
    }

    /**
     * Add store_id to digidirect_aa
     *
     * @return void
     */
    protected function _addAttributeStoreIds()
    {
        $tableName = 'digidirect_aa';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Store ID" column
        $columnName = 'store_id';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Store ID',
                ]
            );
        }

        $this->_connection->addIndex(
            $this->_setup->getTable($tableName),
            $this->_setup->getIdxName(
                $tableName,
                ['attribute_id', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['attribute_id', 'store_id'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );

        // Add "Row ID" column
        $columnName = 'row_id';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'auto_increment' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                    'comment' => 'Row ID',
                ]
            );
        }
    }

    /**
     * Remove unique key from "arrtibute_id" column in digidirect_aa table
     *
     * @return void
     */
    protected function _removeAttributeIdUniqueKeyFromAttributes()
    {
        $tableName = 'digidirect_aa';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        $this->_connection->dropIndex(
            $this->_setup->getTable($tableName),
            $this->_setup->getIdxName(
                $tableName,
                'attribute_id',
                AdapterInterface::INDEX_TYPE_UNIQUE
            )
        );
    }

    /**
     * Add template and custom template fields to attribute
     *
     * @return void
     */
    protected function _addAttributeTemplates()
    {
        $tableName = 'digidirect_aa';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Page Template" column
        $columnName = 'page_template';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Page Template',
                ]
            );
        }

        // Add "Custom Template" column
        $columnName = 'custom_template';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Custom Template',
                ]
            );
        }
    }

    /**
     * Add design fields to option
     *
     * @return void
     */
    protected function _addOptionDesignFields()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "CMS block" column
        $columnName = 'cms_block';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'CMS Block',
                ]
            );
        }

        // Add "Layout Update" column
        $columnName = 'layout_update';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'size' => 255,
                    'comment' => 'Layout Update',
                ]
            );
        }

        // Add "Layout Update XML" column
        $columnName = 'layout_update_xml';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => false,
                    'default' => '',
                    'comment' => 'Layout Update XML',
                ]
            );
        }
    }

    /**
     * Add sort order field to option
     *
     * @return void
     */
    protected function _addOptionSortOrderField()
    {
        $tableName = 'digidirect_aa_options';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Sort Order" column
        $columnName = 'sort_order';
        if (!$this->_isColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Sort Order',
                ]
            );
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
     * Check if column is exists
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
     * Check if column is exists
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    protected function _deleteColumn($tableName, $columnName)
    {
        return $this->_connection->dropColumn($tableName, $columnName);
    }

    /**
     * Check if table is exists
     *
     * @param string $tableName
     * @return bool
     */
    protected function _isTableExists($tableName)
    {
        return $this->_connection->isTableExists($tableName);
    }
}