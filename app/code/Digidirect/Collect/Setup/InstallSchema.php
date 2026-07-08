<?php

namespace Digidirect\Collect\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('quote_item'),
                'collect_place_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Collect Place Id'
                ]
            );
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_order_item'),
                'collect_place_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Collect Place Id'
                ]
            );
        $installer->endSetup();
    }
}
