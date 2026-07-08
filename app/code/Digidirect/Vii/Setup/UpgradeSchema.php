<?php
namespace Digidirect\Vii\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Digidirect\Vii\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return $this
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $installer = $setup;
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->createAbstractGiftCardEntityQuoteTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->createAbstractGiftCardVisitorQuoteTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->updateAbstractGiftCardEntityQuoteTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->createAbstractGiftCardUndoQueueTable($installer);
        }

        $setup->endSetup();
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    protected function createAbstractGiftCardEntityQuoteTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('digidirect_abstract_gift_card_entity_quote'))
            ->addColumn(
                'abstract_gift_card_entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Abstract Gift Card Entity Id'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Quote ID'
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
            ->addColumn(
                'start_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Start Date'
            )
            ->addIndex(
                $installer->getIdxName('digidirect_abstract_gift_card_entity_quote', ['quote_id']),
                ['quote_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'digidirect_abstract_gift_card_entity_quote',
                    ['quote_id', 'abstract_gift_card_entity_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['quote_id', 'abstract_gift_card_entity_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'digidirect_abstract_gift_card_entity_quote',
                    'abstract_gift_card_entity_id',
                    'abstract_gift_card_entity',
                    'entity_id'
                ),
                'abstract_gift_card_entity_id',
                $installer->getTable('abstract_gift_card_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'digidirect_abstract_gift_card_entity_quote',
                    'quote_id',
                    'quote',
                    'entity_id'
                ),
                'quote_id',
                $installer->getTable('quote'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Abstract Gift Card Entity Quote Relation Table');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return void
     */
    protected function createAbstractGiftCardVisitorQuoteTable($installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('digidirect_abstract_gift_card_visitor_quote'))
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Quote ID'
            )
            ->addColumn(
                'visitor_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Visitor Id'
            )
            ->addIndex(
                $installer->getIdxName('digidirect_abstract_gift_card_visitor_quote', ['quote_id']),
                ['quote_id']
            )
            ->addIndex(
                $installer->getIdxName('digidirect_abstract_gift_card_visitor_quote', ['visitor_id']),
                ['visitor_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'digidirect_abstract_gift_card_visitor_quote',
                    'visitor_id',
                    'customer_visitor',
                    'visitor_id'
                ),
                'visitor_id',
                $installer->getTable('customer_visitor'),
                'visitor_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'digidirect_abstract_gift_card_visitor_quote',
                    'quote_id',
                    'quote',
                    'entity_id'
                ),
                'quote_id',
                $installer->getTable('quote'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Abstract Gift Card Quote Relation Table');
        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function updateAbstractGiftCardEntityQuoteTable($setup)
    {
        $installer = $setup;

        if ($setup->tableExists($setup->getTable('digidirect_abstract_gift_card_entity_quote'))) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('digidirect_abstract_gift_card_entity_quote'),
                    'is_queued',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Put Quote To Queue To Cancel PreAuth',
                    ]
                );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createAbstractGiftCardUndoQueueTable($setup)
    {
        $installer = $setup;
        $table = $installer->getConnection()
            ->newTable($installer->getTable('digidirect_abstract_gift_card_undo_queue'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'abstract_gift_card_entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Abstract Gift Card Entity Id'
            )
            ->addColumn(
                'quote_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Quote ID'
            )
            ->addColumn(
                'last_trans_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Last Transaction Id'
            )
            ->addColumn(
                'token',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'PreAuth Token'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                [],
                'Status'
            )
            ->addIndex(
                $installer->getIdxName('digidirect_abstract_gift_card_undo_queue', ['quote_id']),
                ['quote_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'digidirect_abstract_gift_card_undo_queue',
                    ['quote_id', 'abstract_gift_card_entity_id', 'last_trans_id', 'token'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['quote_id', 'abstract_gift_card_entity_id', 'last_trans_id', 'token'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'digidirect_abstract_gift_card_undo_queue',
                    'abstract_gift_card_entity_id',
                    'abstract_gift_card_entity',
                    'entity_id'
                ),
                'abstract_gift_card_entity_id',
                $installer->getTable('abstract_gift_card_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'digidirect_abstract_gift_card_undo_queue',
                    'quote_id',
                    'quote',
                    'entity_id'
                ),
                'quote_id',
                $installer->getTable('quote'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Abstract Gift Card Undo Queue Table');
        $installer->getConnection()->createTable($table);
    }
}
