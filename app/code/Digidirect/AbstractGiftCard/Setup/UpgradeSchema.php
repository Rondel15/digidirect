<?php
namespace Digidirect\AbstractGiftCard\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return $this
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $installer = $setup;
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('abstract_gift_card_entity_order'))
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Order ID'
                )
                ->addColumn(
                    'abstract_gift_card_entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Abstract Gift Card Entity Id'
                )
                ->addColumn(
                    'token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Token'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    1,
                    [],
                    'Status'
                )
                ->addColumn(
                    'amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    '12,4',
                    ['nullable' => false, 'default' => '0.0000'],
                    'Amount'
                )
                ->addIndex(
                    $installer->getIdxName('abstract_gift_card_entity_order', ['order_id']),
                    ['order_id']
                )
                ->addIndex(
                    $installer->getIdxName(
                        'abstract_gift_card_entity_order',
                        ['order_id', 'abstract_gift_card_entity_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['order_id', 'abstract_gift_card_entity_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'abstract_gift_card_entity_order',
                        'abstract_gift_card_entity_id',
                        'abstract_gift_card_entity',
                        'entity_id'
                    ),
                    'abstract_gift_card_entity_id',
                    $installer->getTable('abstract_gift_card_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->setComment('Abstract Gift Card Entity Order Relation Table');
            $installer->getConnection()->createTable($table);
        }

        $setup->endSetup();
        return $this;
    }
}
