<?php
namespace Digidirect\AbstractAttributes\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'digidirect_aa_options'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_aa_options')
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Option Description'
        )->addColumn(
            'image',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Image'
        )->addColumn(
            'url_key',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Url Key'
        )->addColumn(
            'listing',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Is Listing Enabled'
        )->addColumn(
            'option_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Option ID'
        )->addIndex(
            $installer->getIdxName('digidirect_aa_options', ['option_id']),
            ['option_id']
        )->addIndex(
            $installer->getIdxName('digidirect_aa_options', ['url_key']),
            ['url_key'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                $installer->getTable('digidirect_aa_options'),
                'option_id',
                $installer->getTable('eav_attribute_option'),
                'option_id'
            ),
            'option_id',
            $installer->getTable('eav_attribute_option'),
            'option_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Abstract Attributes Options Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'digidirect_aa'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_aa')
        )->addColumn(
            'status',
            Table::TYPE_BOOLEAN,
            null,
            ['nullable' => false, 'default' => 0],
            'Is Enabled'
        )->addColumn(
            'attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Attribute ID'
        )->addIndex(
            $installer->getIdxName('digidirect_aa', ['attribute_id']),
            ['attribute_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addForeignKey(
            $installer->getFkName(
                $installer->getTable('digidirect_aa'),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Abstract Attributes Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
