<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mageplaza\Shopbybrand\Helper\Data;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package Mageplaza\Shopbybrand\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * InstallSchema constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.4.0', '<')) {
            if (!$installer->tableExists('mageplaza_shopbybrand_category')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_shopbybrand_category'))
                    ->addColumn('cat_id', Table::TYPE_INTEGER, 10, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ])
                    ->addColumn('name', Table::TYPE_TEXT, '256')
                    ->addColumn('status', Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1])
                    ->addColumn('url_key', Table::TYPE_TEXT, '255')
                    ->addColumn('store_ids', Table::TYPE_TEXT, '255', ['nullable' => false, 'default' => '0'])
                    ->addColumn('meta_title', Table::TYPE_TEXT, '256')
                    ->addColumn('meta_keywords', Table::TYPE_TEXT, '64k')
                    ->addColumn('meta_description', Table::TYPE_TEXT, '2M')
                    ->addColumn('meta_robots', Table::TYPE_TEXT, null, [], 'Category Meta Robots')
                    ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Category Created At')
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Tag Updated At')
                    ->addIndex(
                        $installer->getIdxName('mageplaza_shopbybrand_category', 'url_key'),
                        'url_key',
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Mageplaza Shopbybrand category table');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_shopbybrand_brand_category')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_shopbybrand_brand_category'))
                    ->addColumn('cat_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ])
                    ->addColumn(
                        'option_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'unsigned' => true, 'primary' => true]
                    )
                    ->addColumn('position', Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0])
                    ->addIndex(
                        $installer->getIdxName('mageplaza_shopbybrand_brand_category', ['option_id']),
                        ['option_id']
                    )
                    ->addIndex($installer->getIdxName('mageplaza_shopbybrand_brand_category', ['cat_id']), ['cat_id'])
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_shopbybrand_brand_category',
                            'option_id',
                            'eav_attribute_option',
                            'option_id'
                        ),
                        'option_id',
                        $installer->getTable('eav_attribute_option'),
                        'option_id',
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_shopbybrand_brand_category',
                            'cat_id',
                            'mageplaza_shopbybrand_category',
                            'cat_id'
                        ),
                        'cat_id',
                        $installer->getTable('mageplaza_shopbybrand_category'),
                        'cat_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_shopbybrand_brand_category',
                            ['option_id', 'cat_id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['option_id', 'cat_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Mageplaza Shopbybrand brand category table');

                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_brand_reports_reindex')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_brand_reports_reindex'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ], 'Id')
                    ->addColumn('code', Table::TYPE_TEXT, 255, [], 'Reports Code')
                    ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Reports Name')
                    ->addColumn('description', Table::TYPE_TEXT, 255, [], 'Reports Description')
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Updated At')
                    ->setComment('Mageplaza Brand Reports Reindex Table');
                $installer->getConnection()->createTable($table);
            }

            if (!$installer->tableExists('mageplaza_brand_report')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_brand_report'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true
                    ], 'Id')
                    ->addColumn('order_created_at', Table::TYPE_DATE, null, [], 'Period')
                    ->addColumn('store_id', Table::TYPE_INTEGER, null, [], 'Store Id')
                    ->addColumn('status', Table::TYPE_TEXT, 10, [], 'Order Status')
                    ->addColumn('order_id', Table::TYPE_INTEGER, null, [], 'Order Id')
                    ->addColumn('qty_order', Table::TYPE_INTEGER, null, [], 'Total Qty Order')
                    ->addColumn('qty_item', Table::TYPE_INTEGER, null, [], 'Qty Item')
                    ->addColumn('row_total', Table::TYPE_DECIMAL, '20,04', [], 'Row Total')
                    ->addColumn('discount', Table::TYPE_DECIMAL, '20,04', [], 'Discount')
                    ->addColumn('tax', Table::TYPE_DECIMAL, '20,04', [], 'Tax')
                    ->addColumn('refunded', Table::TYPE_DECIMAL, '20,04', [], 'Refunded')
                    ->addColumn('canceled', Table::TYPE_DECIMAL, '20,04', [], 'Canceled')
                    ->addColumn('product_id', Table::TYPE_INTEGER, 10, [], 'Product Id')
                    ->addColumn('item_id', Table::TYPE_INTEGER, 10, [], 'Product Id')
                    ->addColumn('product_name', Table::TYPE_TEXT, 255, [], 'Product Name')
                    ->addColumn('sku', Table::TYPE_TEXT, 255, [], 'SKU')
                    ->addColumn('attribute_id', Table::TYPE_INTEGER, 10, [], 'Attribute Id')
                    ->addColumn('attribute_code', Table::TYPE_TEXT, 255, [], 'Attribute Code')
                    ->addColumn('attribute_value', Table::TYPE_TEXT, 255, [], 'Attribute Value')
                    ->addColumn('attribute_name', Table::TYPE_TEXT, 255, [], 'Attribute Name')
                    ->setComment('Mageplaza Reports Sales By Brand');
                $installer->getConnection()->createTable($table);
            }
            if ($installer->tableExists('mageplaza_brand')) {
                $eavTable = $installer->getTable('mageplaza_brand');
                $connection = $installer->getConnection();
                $connection->addColumn($eavTable, 'is_display', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'size' => 1,
                    'default' => 1,
                    'nullable' => true,
                    'comment' => 'Display Brand',
                ]);
                $connection->addColumn($eavTable, 'related_brands', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'size' => 255,
                    'nullable' => true,
                    'comment' => 'Related Brands',
                ]);
            }
        }

        $installer->endSetup();
    }
}
