<?php

namespace Digidirect\AddressVerification\Setup;

use Digidirect\AddressVerification\Model\ResourceModel\ImportReport;
use Digidirect\AddressVerification\Model\ResourceModel\Location;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 *
 * @package Digidirect\AddressVerification\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->createLocationTable($setup);
            $this->createTableImportReport($setup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->upgradeLocationTable($setup);
            $this->createCountryAddressAttribute($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->upgradeLocationReportTable($setup);
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            $this->upgradeImportTable($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     * @return string
     */
    protected function getLocationsTable(SchemaSetupInterface $installer)
    {
        return $installer->getTable('digidirect_addressverification_locations');
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function upgradeImportTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $this->getLocationsTable($installer);
        if ($installer->getConnection()->isTableExists($tableName)
            && $installer->getConnection()->tableColumnExists($tableName, 'postcode')
        ) {
            $installer->getConnection()->modifyColumn(
                $tableName,
                'postcode',
                [
                    'default' => null,
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Postcode',
                    'length' => 20
                ]
            );
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    public function createCountryAddressAttribute(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_addressverification_country_address_attribute')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'country_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Country Code'
        )->addColumn(
            'attributes',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Attributes'
        )->addIndex(
            $installer->getIdxName('digidirect_addressverification_country_address_attribute', ['country_code']),
            ['country_code'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    public function upgradeLocationTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('digidirect_addressverification_locations'),
            'country_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'comment' => 'Country Code',
            ]
        );

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    public function upgradeLocationReportTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('digidirect_addressverification_import_report'),
            'country_code',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'comment' => 'Country Code',
            ]
        );

        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function createLocationTable(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_addressverification_locations')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Website ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Store ID'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            ['nullable' => true, 'default' => null],
            'Zip/Postal Code'
        )->addColumn(
            'suburb',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => true, 'default' => null],
            'Suburb'
        )
            ->addIndex($installer->getIdxName(Location::MAIN_TABLE, ['store_id']), ['store_id'])
            ->addIndex($installer->getIdxName(Location::MAIN_TABLE, ['website_id']), ['website_id'])
            ->addForeignKey(
                $installer->getFkName(Location::MAIN_TABLE, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(Location::MAIN_TABLE, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
        return $this;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function createTableImportReport(SchemaSetupInterface $setup)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('digidirect_addressverification_import_report')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Website ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Store ID'
        )->addColumn(
            'type',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'File Type'
        )->addColumn(
            'last_import_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            5,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Last Import Time'
        )
            ->addIndex($installer->getIdxName(ImportReport::MAIN_TABLE, ['store_id']), ['store_id'])
            ->addIndex($installer->getIdxName(ImportReport::MAIN_TABLE, ['website_id']), ['website_id'])
            ->addForeignKey(
                $installer->getFkName(ImportReport::MAIN_TABLE, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(ImportReport::MAIN_TABLE, 'website_id', 'store_website', 'website_id'),
                'website_id',
                $installer->getTable('store_website'),
                'website_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
        return $this;
    }
}
