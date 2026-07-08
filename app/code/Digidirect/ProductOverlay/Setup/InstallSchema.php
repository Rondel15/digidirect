<?php

namespace Digidirect\ProductOverlay\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Digidirect\ProductOverlay\Model\Overlays;

/**
 * Class InstallSchema
 * @package Digidirect\ProductOverlay\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $table = $installer->getConnection()->newTable($installer->getTable('digidirect_product_overlay'));
        $table = $this->_addColumns($table);
        $installer->getConnection()->createTable($table);
        $installer->endSetup();

        return $this;
    }

    /**
     * @param mixed $table
     * @return mixed
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _addColumns($table)
    {
        $table
            ->addColumn(Overlays::OVERLAY_ID, Table::TYPE_INTEGER, null, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ], 'Id')
            ->addColumn(Overlays::POS, Table::TYPE_INTEGER, null, ['default' => 0, 'nullable' => false], 'Position')
            ->addColumn(Overlays::IS_SINGLE, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Is Single')
            ->addColumn(Overlays::NAME, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Name')
            ->addColumn(Overlays::STATUS, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Status')
            ->addColumn(Overlays::STORES, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Stores')
            ->addColumn(Overlays::PROD_TXT, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Product text')
            ->addColumn(Overlays::PROD_IMG, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Product image')
            ->addColumn(Overlays::PROD_IMAGE_SIZE, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Product image size')
            ->addColumn(Overlays::PROD_POS, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Product position')
            ->addColumn(Overlays::CAT_TXT, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Category text')
            ->addColumn(Overlays::CAT_IMG, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Category image')
            ->addColumn(Overlays::CAT_POS, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Category position')
            ->addColumn(Overlays::CAT_IMAGE_SIZE, Table::TYPE_TEXT, null, [
                'default'  => '',
                'nullable' => false
            ], 'Category image size')
            ->addColumn(Overlays::IS_NEW, Table::TYPE_SMALLINT, null, ['default' => 0, 'nullable' => false], 'Is new')
            ->addColumn(Overlays::IS_SALE, Table::TYPE_SMALLINT, null, ['default' => 0, 'nullable' => false], 'Is sale')
            ->addColumn(Overlays::SPECIAL_PRICE_ONLY, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Special Price Only')
            ->addColumn(Overlays::STOCK_LESS, Table::TYPE_INTEGER, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Stock less')
            ->addColumn(Overlays::STOCK_MORE, Table::TYPE_INTEGER, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Stock more')
            ->addColumn(Overlays::STOCK_STATUS, Table::TYPE_INTEGER, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Stock status')
            ->addColumn(Overlays::FROM_DATE, Table::TYPE_DATETIME, null, ['nullable' => false], 'From Date')
            ->addColumn(Overlays::TO_DATE, Table::TYPE_DATETIME, null, ['nullable' => false], 'To Date')
            ->addColumn(Overlays::DATE_RANGE_ENABLED, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Date range enabled')
            ->addColumn(Overlays::FROM_PRICE, Table::TYPE_DECIMAL, null, [
                'default'  => '0.0000',
                'nullable' => false
            ], 'From price')
            ->addColumn(Overlays::TO_PRICE, Table::TYPE_DECIMAL, null, [
                'default'  => '0.0000',
                'nullable' => false
            ], 'To price')
            ->addColumn(Overlays::BY_PRICE, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'By price')
            ->addColumn(Overlays::PRICE_RANGE_ENABLED, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Price range enabled')
            ->addColumn(Overlays::CUSTOMER_GROUP_IDS, Table::TYPE_TEXT, null, ['nullable' => false], 'Customer groups')
            ->addColumn(Overlays::COND_SERIALIZE, Table::TYPE_TEXT, null, ['nullable' => false], 'Conditions')
            ->addColumn(Overlays::CUSTOMER_GROUP_ENABLED, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Customer group enabled')
            ->addColumn(Overlays::USE_FOR_PARENT, Table::TYPE_SMALLINT, null, [
                'default'  => 0,
                'nullable' => false
            ], 'Use for parent');

        return $table;
    }
}
