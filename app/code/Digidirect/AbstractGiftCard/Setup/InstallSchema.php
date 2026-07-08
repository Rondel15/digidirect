<?php
namespace Digidirect\AbstractGiftCard\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

        $table = $installer->getConnection()
            ->newTable($installer->getTable('abstract_gift_card_entity'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'service_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Service Code'
            )
            ->addColumn(
                'giftcard_account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'GiftCard Account Id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                [],
                'Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Gift Card Code'
            )
            ->addColumn(
                'pin',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Gift Card Pin'
            )
            ->addIndex(
                $installer->getIdxName('abstract_gift_card_entity', ['giftcard_account_id']),
                ['giftcard_account_id']
            )
            ->addIndex(
                $installer->getIdxName('abstract_gift_card_entity', ['code']),
                ['code']
            )
            ->addIndex(
                $installer->getIdxName('abstract_gift_card_entity', ['pin']),
                ['pin']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'abstract_gift_card_entity',
                    'giftcard_account_id',
                    'magento_giftcardaccount',
                    'giftcardaccount_id'
                ),
                'giftcard_account_id',
                $installer->getTable('magento_giftcardaccount'),
                'giftcardaccount_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Abstract Gift Card Entity Table');
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
