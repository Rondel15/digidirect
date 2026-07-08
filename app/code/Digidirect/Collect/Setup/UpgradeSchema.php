<?php
namespace Digidirect\Collect\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Digidirect\Collect\Api\Data\PostCodeInterface as postCodeInterface;

/**
 * Class UpgradeSchema
 *
 * @package Digidirect\AI\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * upgrade
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote_item'),
                    'collect_place_storage_name',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Collect Place Storage Name'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('sales_order_item'),
                    'collect_place_storage_name',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'Collect Place Storage Name'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('quote_address'),
                    'is_collect',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => true,
                        'comment' => 'Is Collect Address'
                    ]
                );
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('sales_order_address'),
                    'is_collect',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => true,
                        'comment' => 'Is Collect Address'
                    ]
                );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->createTablePostCode($setup);
        }
        $setup->endSetup();
    }

    /**
     * createTablePostCode
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Exception
     */
    public function createTablePostCode(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable(postCodeInterface::POST_CODE_TABLE)
        )
            ->addColumn(
                postCodeInterface::TABLE_COLUMN_ID,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_POST_CODE,
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => false, 'unsigned' => true, 'primary' => false, 'nullable' => false],
                'Post Code'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_LOCALITY,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                45,
                ['default' => null, 'primary' => false, 'nullable' => false],
                'Locality'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_STATE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                4,
                ['nullable' => true, 'default' => null],
                'State'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_COMMENTS,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Comments'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_CATEGORY,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => null],
                'Category'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_LONGITUDE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable' => true, 'default' => null],
                'Longitude'
            )->addColumn(
                postCodeInterface::TABLE_COLUMN_LATITUDE,
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                25,
                ['nullable' => true, 'default' => null],
                'Latitude'
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
        return $this;
    }
}
