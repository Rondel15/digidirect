<?php
namespace Digidirect\RelatedProduct\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @author Digidirect team
 * @package Digidirect\RelatedProduct\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    const CUSTOM_GALLERY_VALUE_TABLE = 'digidirect_catalog_product_entity_media_gallery_value';

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'digidirect_catalog_product_entity_media_gallery_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::CUSTOM_GALLERY_VALUE_TABLE))
            ->addColumn(
                'value_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )
            ->addColumn(
                'featured_product_image',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Feature brand image'
            )
            ->addForeignKey(
                $installer->getFkName(
                    self::CUSTOM_GALLERY_VALUE_TABLE,
                    'value_id',
                    'catalog_product_entity_media_gallery',
                    'value_id'
                ),
                'value_id',
                $installer->getTable('catalog_product_entity_media_gallery'),
                'value_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::CUSTOM_GALLERY_VALUE_TABLE, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Digidirect Catalog Product Media Gallery Custom Attribute Value Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
