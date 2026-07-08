<?php
namespace Digidirect\MyStoreWidget\Setup;

use Digidirect\MyStoreWidget\Api\Data\MyStoreInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package Digidirect\MyStoreWidget\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $customerStoreTable = $setup->getTable('digidirect_mystorewidget_customer_store');

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $setup->getConnection()->dropForeignKey(
                $customerStoreTable,
                $setup->getFkName(
                    $customerStoreTable,
                    'customer_id',
                    'customer_entity',
                    'entity_id'
                )
            );
            $setup->getConnection()->dropIndex(
                $customerStoreTable,
                $setup->getIdxName(
                    $customerStoreTable,
                    'customer_id'
                )
            );
            $setup->getConnection()->addColumn(
                $customerStoreTable,
                'search_text',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Search Text'
                ]
            );
            $setup->getConnection()->addColumn(
                $customerStoreTable,
                'type',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'comment' => 'Type',
                    'nullable' => false,
                    'default' => ''
                ]
            );
            $indexFields = ['customer_id', 'type'];
            $setup->getConnection()->addIndex(
                $customerStoreTable,
                $setup->getIdxName(
                    $customerStoreTable,
                    $indexFields
                ),
                $indexFields,
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }
    }
}
