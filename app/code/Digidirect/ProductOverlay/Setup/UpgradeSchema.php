<?php
namespace Digidirect\ProductOverlay\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Digidirect\ProductOverlay\Api\Data\OverlayInterface;

/**
 * Class UpgradeSchema
 *
 * @package Digidirect\Faq\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->updatePrice($setup);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addStoreOverlayTable($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addPrivateSalesColumns($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->addStockColumns($setup);
        }

        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $this->addStockLabelColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $this->createCatalogruleAssociationTable($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addStockLabelColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('digidirect_product_overlay');
        $setup->getConnection()->addColumn(
            $tableName,
            OverlayInterface::PROD_STOCK_LABEL,
            [
                'type' => Table::TYPE_TEXT,
                'default'  => '',
                'nullable' => true,
                'comment' => 'Product Stock Label'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addStockColumns(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('digidirect_product_overlay');
        if ($setup->getConnection()->tableColumnExists($tableName, OverlayInterface::STOCK_FROM)) {
            $setup->getConnection()->dropColumn($tableName, OverlayInterface::STOCK_FROM);
        }

        $setup->getConnection()->addColumn(
            $tableName,
            OverlayInterface::STOCK_FROM,
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => null,
                'comment' => 'Stock minimal condition'
            ]
        );

        if ($setup->getConnection()->tableColumnExists($tableName, OverlayInterface::STOCK_TO)) {
            $setup->getConnection()->dropColumn($tableName, OverlayInterface::STOCK_TO);
        }

        $setup->getConnection()->addColumn(
            $tableName,
            OverlayInterface::STOCK_TO,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Stock maximal condition'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addPrivateSalesColumns(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('digidirect_product_overlay');
        $setup->getConnection()->addColumn(
            $tableName,
            OverlayInterface::PRIVATE_SALES_ENABLED,
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'default' => 0,
                'comment' => 'Price rules enabled'
            ]
        );

        $setup->getConnection()->addColumn(
            $tableName,
            OverlayInterface::CATALOG_PRICE_RULES_IDS,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'default' => null,
                'comment' => 'Catalog Price Rule IDs'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function updatePrice(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('digidirect_product_overlay');
        $setup->getConnection()->modifyColumn(
            $tableName,
            'to_price',
            [
                'type' => Table::TYPE_DECIMAL,
                'unsigned' => false,
                'nullable' => true,
                'comment' => 'To Price',
            ]
        );
        return $this;
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    protected function addStoreOverlayTable(SchemaSetupInterface $installer)
    {
        $this->removeColumn($installer);

        $stringTableName = 'digidirect_product_overlay_store';
        $tableName = $installer->getTable($stringTableName);
        if (!($installer->tableExists($tableName))) {
            $storeOverlayTable = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'overlay_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Overlay ID'
                )->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                    ],
                    'Store ID'
                )
                ->addIndex(
                    $installer->getIdxName($stringTableName, ['store_id']),
                    ['store_id']
                )->addForeignKey(
                    $installer->getFkName($stringTableName, 'overlay_id', $stringTableName, 'overlay_id'),
                    'overlay_id',
                    $installer->getTable('digidirect_product_overlay'),
                    'overlay_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->addForeignKey(
                    $installer->getFkName($stringTableName, 'store_id', 'store', 'store_id'),
                    'store_id',
                    $installer->getTable('store'),
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment(
                    'Overlay store table'
                );
            $installer->getConnection()->createTable($storeOverlayTable);
        }
        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function removeColumn(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('digidirect_product_overlay');
        $setup->getConnection()->dropColumn($tableName, 'stores');
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function createCatalogruleAssociationTable(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'catalogrule_customer_group'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('digidirect_product_overlay_catalogrule'))
            ->addColumn(
                'row_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Row Id'
            )
            ->addColumn(
                'overlay_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Customer Group Id'
            )
            ->addIndex(
                $setup->getIdxName('digidirect_product_overlay_catalogrule', ['overlay_id']),
                ['overlay_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    'digidirect_product_overlay_catalogrule',
                    'row_id',
                    'catalogrule',
                    'row_id'
                ),
                'row_id',
                $setup->getTable('catalogrule'),
                'row_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $setup->getFkName(
                    'digidirect_product_overlay_catalogrule',
                    'overlay_id',
                    'digidirect_product_overlay',
                    'overlay_id'
                ),
                'overlay_id',
                $setup->getTable('digidirect_product_overlay'),
                'overlay_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Product Overlay ro Catalog Rule Relations');

        $setup->getConnection()->createTable($table);
    }
}
