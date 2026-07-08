<?php

namespace Digidirect\Feed\Setup;

use Magento\Framework\DB\Ddl\Table;
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
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_feed')
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Feed Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'filename',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Filename'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Type'
        )->addColumn(
            'format_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => true],
            'Format'
        )->addColumn(
            'is_active',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Is Active'
        )->addColumn(
            'generated_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true],
            'Generated At'
        )->addColumn(
            'generated_cnt',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Generated Cnt'
        )->addColumn(
            'generated_time',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Generated Time'
        )->addColumn(
            'cron',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Cron'
        )->addColumn(
            'cron_day',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Cron Day'
        )->addColumn(
            'cron_time',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Cron Time'
        )->addColumn(
            'ftp',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Ftp'
        )->addColumn(
            'ftp_protocol',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ftp Protocol'
        )->addColumn(
            'ftp_host',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ftp Host'
        )->addColumn(
            'ftp_user',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ftp User'
        )->addColumn(
            'ftp_password',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ftp Password'
        )->addColumn(
            'ftp_path',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ftp Path'
        )->addColumn(
            'ftp_passive_mode',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Ftp Passive Mode'
        )->addColumn(
            'uploaded_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => true],
            'Uploaded At'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Updated At'
        )->addColumn(
            'ga_source',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ga Source'
        )->addColumn(
            'ga_medium',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ga Medium'
        )->addColumn(
            'ga_name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ga Name'
        )->addColumn(
            'ga_term',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ga Term'
        )->addColumn(
            'ga_content',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Ga Content'
        )->addColumn(
            'notification_emails',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Notification Emails'
        )->addColumn(
            'notification_events',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Notifications Events'
        )->addColumn(
            'report_enabled',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Report Enabled?'
        )->addColumn(
            'archivation',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Archivation'
        )->addIndex(
            $installer->getIdxName('digidirect_feed_feed', ['store_id']),
            ['store_id']
        )->addIndex(
            $installer->getIdxName(
                'digidirect_feed_feed',
                ['type', 'filename'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['type', 'filename'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_feed',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_feed_history')
        )->addColumn(
            'history_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'History Id'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Feed Id'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Type'
        )->addColumn(
            'title',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Title'
        )->addColumn(
            'message',
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => true],
            'Message'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Updated At'
        )->addIndex(
            $installer->getIdxName('digidirect_feed_feed_history', ['feed_id']),
            ['feed_id']
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_feed_history',
                'feed_id',
                'digidirect_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('digidirect_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_feed_product')
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => false, 'primary' => true],
            'Feed Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => false, 'primary' => true],
            'Product Id'
        )->addIndex(
            $installer->getIdxName('digidirect_feed_feed_product', ['product_id']),
            ['product_id']
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_feed_product',
                'feed_id',
                'digidirect_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('digidirect_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_feed_product',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_mapping_category')
        )->addColumn(
            'mapping_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Mapping Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            \Digidirect\Feed\Model\Dynamic\Attribute::CODE_MAX_LENGTH,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'mapping_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => false],
            'Mapping Serialized'
        )->addIndex(
            $installer->getIdxName(
                'digidirect_feed_mapping_category',
                ['code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_rule')
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => false],
            'Conditions Serialized'
        )->addColumn(
            'actions_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => false],
            'Actions Serialized'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_rule_feed')
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => false, 'primary' => true],
            'Feed Id'
        )->addIndex(
            $installer->getIdxName('digidirect_feed_rule_feed', ['feed_id']),
            ['feed_id']
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_rule_feed',
                'feed_id',
                'digidirect_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('digidirect_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_rule_feed',
                'rule_id',
                'digidirect_feed_rule',
                'rule_id'
            ),
            'rule_id',
            $installer->getTable('digidirect_feed_rule'),
            'rule_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_rule_product')
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => false, 'primary' => true],
            'Product Id'
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_rule_product',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_rule_product',
                'rule_id',
                'digidirect_feed_rule',
                'rule_id'
            ),
            'rule_id',
            $installer->getTable('digidirect_feed_rule'),
            'rule_id',
            Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_template')
        )->addColumn(
            'template_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'identity' => true, 'primary' => true],
            'Template Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'type',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Type'
        )->addColumn(
            'format_serialized',
            Table::TYPE_TEXT,
            '64K',
            ['nullable' => true],
            'Format'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addColumn(
            'updated_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Updated At'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_dynamic_attribute')
        )->addColumn(
            'attribute_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            \Digidirect\Feed\Model\Dynamic\Attribute::CODE_MAX_LENGTH,
            ['nullable' => false],
            'Code'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Conditions'
        )->addIndex(
            $installer->getIdxName(
                'digidirect_feed_dynamic_attribute',
                ['code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment('Dynamic Attributes');

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_feed_report')
        )->addColumn(
            'row_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Row Id'
        )->addColumn(
            'session',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Session'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Feed Id'
        )->addColumn(
            'product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Product Id'
        )->addColumn(
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => true],
            'Order Id'
        )->addColumn(
            'subtotal',
            Table::TYPE_DECIMAL,
            '12,4',
            ['unsigned' => true, 'nullable' => true],
            'Order subtotal (for product)'
        )->addColumn(
            'is_click',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Is Click?'
        )->addColumn(
            'is_order',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Is Order?'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => 0],
            'Store Id'
        )->addColumn(
            'created_at',
            Table::TYPE_DATETIME,
            null,
            ['nullable' => false],
            'Created At'
        )->addIndex(
            $installer->getIdxName('digidirect_feed_report', ['created_at']),
            ['created_at']
        )->addIndex(
            $installer->getIdxName('digidirect_feed_report', ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName('digidirect_feed_report', ['feed_id']),
            ['feed_id']
        )->addIndex(
            $installer->getIdxName(
                'digidirect_feed_report',
                ['order_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_id'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_report',
                'feed_id',
                'digidirect_feed_feed',
                'feed_id'
            ),
            'feed_id',
            $installer->getTable('digidirect_feed_feed'),
            'feed_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_report',
                'product_id',
                'catalog_product_entity',
                'entity_id'
            ),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_report',
                'order_id',
                'sales_order',
                'entity_id'
            ),
            'order_id',
            $installer->getTable('sales_order'),
            'entity_id',
            Table::ACTION_SET_NULL
        )->addForeignKey(
            $installer->getFkName(
                'digidirect_feed_report',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            Table::ACTION_SET_DEFAULT
        )->setComment('Feed Report');

        $installer->getConnection()->createTable($table);
    }
}
