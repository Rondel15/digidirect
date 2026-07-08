<?php
namespace Digidirect\CheckoutFields\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use \Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Add column "delivery_date" to quote table
     * Add column "delivery_date" to sales_order table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $setup->getTable('digidirect_checkout_fields_quote_field_value');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()->newTable(
                $tableName
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'entity id'
            )->addColumn(
                'code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'code'
            )->addColumn(
                'quote_id',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'unsigned' => true],
                'quote id'
            )->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                [],
                'value'
            )->addForeignKey(
                $installer->getFkName(
                    'digidirect_checkout_fields_quote_field_value',
                    'quote_id',
                    'quote',
                    'entity_id'
                ),
                'quote_id',
                $installer->getTable('quote'),
                'entity_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->setComment('Digidirect quote field value');
            $installer->getConnection()->createTable($table);
        }

        $tableName = $setup->getTable('digidirect_checkout_fields_order_field_value');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $installer->getConnection()->newTable(
                $tableName
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'entity id'
            )->addColumn(
                'code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'code'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'unsigned' => true],
                'order id'
            )->addColumn(
                'value',
                Table::TYPE_TEXT,
                null,
                [],
                'value'
            )->addForeignKey(
                $installer->getFkName(
                    'digidirect_checkout_fields_order_field_value',
                    'order_id',
                    'sales_order',
                    'entity_id'
                ),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            )
            ->setComment('Digidirect order field value');
            $installer->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
