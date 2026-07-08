<?php
namespace Digidirect\ExtendedShippingRates\Setup;

use Digidirect\ExtendedShippingRates\Model\Carrier;
use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\ExtendedShippingRates\Model\Zone;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addMainTable($setup);
        $this->addStoreTable($setup);
        $this->addCustomerGroupTable($setup);
        $this->addCarriersTable($setup);
        $this->addMethodsTable($setup);
        $this->addLabelsTables($setup);
        $this->addMethodRatesTable($setup);
        $this->addZonesTable($setup);
        $this->addZonesStoreViewTable($setup);

        $setup->endSetup();
    }

    /**
     * Adds main table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    protected function addMainTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Rule::RULE_TABLE_NAME)
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            'description',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Description'
        )->addColumn(
            'from_date',
            Table::TYPE_DATE,
            null,
            ['nullable' => true, 'default' => null],
            'From'
        )->addColumn(
            'to_date',
            Table::TYPE_DATE,
            null,
            ['nullable' => true, 'default' => null],
            'To'
        )->addColumn(
            'days_of_week',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Days of Week (Available On)'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions Serialized'
        )->addColumn(
            'actions_serialized',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Actions Serialized'
        )->addColumn(
            'stop_rules_processing',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Stop Rules Processing'
        )->addColumn(
            'shipping_methods',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Shipping Methods'
        )->addColumn(
            'disabled_shipping_methods',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Disabled Shipping Methods'
        )->addColumn(
            'enabled_shipping_methods',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Enabled Shipping Methods'
        )->addColumn(
            'sort_order',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Sort Order (Priority)'
        )->addColumn(
            'action_type',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Action Type'
        )->addColumn(
            'simple_action',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Simple Action'
        )->addColumn(
            'amount',
            Table::TYPE_TEXT,
            '2M',
            []
        )->addColumn(
            'time_from',
            Table::TYPE_INTEGER,
            null,
            [],
            'Time From'
        )->addColumn(
            'time_to',
            Table::TYPE_INTEGER,
            null,
            [],
            'Time To'
        )->addColumn(
            'use_time',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Use or not time flag'
        )->addColumn(
            'time_enabled',
            Table::TYPE_SMALLINT,
            null,
            [],
            'Time Range is enabled or disabled time'
        )->addIndex(
            $setup->getIdxName('digidirect_extendedshippingrates', ['is_active', 'sort_order', 'to_date', 'from_date']),
            ['is_active', 'sort_order', 'to_date', 'from_date']
        )->setComment(
            'Digidirect Extended Shipping Rates'
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * Create table 'digidirect_extendedshippingrates_store' if not exists. This table will be used instead of
     * column store_ids of main catalog shipping rules table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function addStoreTable(SchemaSetupInterface $setup)
    {
        $storeTable = $setup->getTable('store');
        $rulesStoresTable = $setup->getTable(Rule::STORE_TABLE_NAME);

        $table = $setup->getConnection()->newTable(
            $rulesStoresTable
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store View Id'
        )->addIndex(
            $setup->getIdxName('digidirect_extendedshippingrates_store', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName(
                'digidirect_extendedshippingrates_store',
                'rule_id',
                'digidirect_extendedshippingrates',
                'rule_id'
            ),
            'rule_id',
            $setup->getTable(Rule::RULE_TABLE_NAME),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName('digidirect_extendedshippingrates_store', 'store_id', 'store', 'store_id'),
            'store_id',
            $storeTable,
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Shipping Rules To Stores Relations'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * Create table 'digidirect_extendedshippingrates_customer_group' if not exists. This table will be used instead of
     * column customer_group_ids of main shipping rules table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    protected function addCustomerGroupTable(SchemaSetupInterface $setup)
    {
        $customerGroupsTable = $setup->getTable('customer_group');
        $rulesCustomerGroupsTable = $setup->getTable(Rule::CUSTOMER_GROUP_TABLE_NAME);

        $table = $setup->getConnection()->newTable(
            $rulesCustomerGroupsTable
        )->addColumn(
            'rule_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'customer_group_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Group Id'
        )->addIndex(
            $setup->getIdxName('digidirect_extendedshippingrates_customer_group', ['customer_group_id']),
            ['customer_group_id']
        )->addForeignKey(
            $setup->getFkName(
                'digidirect_extendedshippingrates_customer_group',
                'rule_id',
                'digidirect_extendedshippingrates',
                'rule_id'
            ),
            'rule_id',
            $setup->getTable(Rule::RULE_TABLE_NAME),
            'rule_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                'digidirect_extendedshippingrates_customer_group',
                'customer_group_id',
                'customer_group',
                'customer_group_id'
            ),
            'customer_group_id',
            $customerGroupsTable,
            'customer_group_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Shipping Rules To Customer Groups Relations'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * Adds carriers table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    protected function addCarriersTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(Carrier::CARRIER_TABLE_NAME))
            ->addColumn(
                'carrier_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'carrier_code',
                Table::TYPE_TEXT,
                64,
                [],
                'Carrier Code'
            )
            ->addColumn(
                'active',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'sallowspecific',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'sallowspecific'
            )
            ->addColumn(
                'model',
                Table::TYPE_TEXT,
                mb_strlen(Carrier::DEFAULT_MODEL),
                ['default' => Carrier::DEFAULT_MODEL],
                'Corresponding Model'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Carrier Name'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Carrier Title'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Carrier Type'
            )
            ->addColumn(
                'specificerrmsg',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Carrier Error Message'
            )
            ->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Default Price'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex(
                $setup->getIdxName(Carrier::CARRIER_TABLE_NAME, ['carrier_code']),
                ['carrier_code']
            )
            ->addIndex(
                $setup->getIdxName(Carrier::CARRIER_TABLE_NAME, ['carrier_id']),
                ['carrier_id']
            )
            ->addIndex(
                $setup->getIdxName(
                    Carrier::CARRIER_TABLE_NAME,
                    ['carrier_id', 'carrier_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['carrier_id', 'carrier_code'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Artificial Carriers Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Adds methods table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws \Zend_Db_Exception
     */
    protected function addMethodsTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(Carrier::METHOD_TABLE_NAME))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'code',
                Table::TYPE_TEXT,
                64,
                [],
                'Method Code'
            )
            ->addColumn(
                'carrier_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Carrier ID'
            )
            ->addColumn(
                'active',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Method Title'
            )
            ->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Default Price'
            )
            ->addColumn(
                'cost',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Default Cost'
            )
            ->addColumn(
                'packaging_weight_type',
                Table::TYPE_TEXT,
                64,
                [],
                'Packaging Weight Type'
            )
            ->addColumn(
                'packaging_weight_value',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Packaging Weight Value'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex(
                $setup->getIdxName(
                    Carrier::METHOD_TABLE_NAME,
                    ['entity_id', 'code', 'carrier_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'code', 'carrier_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(Carrier::METHOD_TABLE_NAME, ['entity_id']),
                ['entity_id']
            )
            ->addIndex(
                $setup->getIdxName(Carrier::METHOD_TABLE_NAME, ['code']),
                ['code']
            )
            ->addIndex(
                $setup->getIdxName(Carrier::METHOD_TABLE_NAME, ['carrier_id']),
                ['carrier_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    Carrier::METHOD_TABLE_NAME,
                    'carrier_id',
                    Carrier::CARRIER_TABLE_NAME,
                    'carrier_id'
                ),
                'carrier_id',
                $setup->getTable(Carrier::CARRIER_TABLE_NAME),
                'carrier_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Artificial Carriers Methods Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Adds labels tables for the methods and carriers
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function addLabelsTables(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'digidirect_extendedshippingrates_carrier_label'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Carrier::CARRIER_LABELS_TABLE_NAME)
        )->addColumn(
            'label_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Label Id'
        )->addColumn(
            'carrier_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Carrier Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'label',
            Table::TYPE_TEXT,
            255,
            [],
            'Label'
        )->addIndex(
            $setup->getIdxName(
                Carrier::CARRIER_LABELS_TABLE_NAME,
                ['carrier_id', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['carrier_id', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(Carrier::CARRIER_LABELS_TABLE_NAME, ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName(
                Carrier::CARRIER_LABELS_TABLE_NAME,
                'carrier_id',
                Carrier::CARRIER_TABLE_NAME,
                'carrier_id'
            ),
            'carrier_id',
            $setup->getTable(Carrier::CARRIER_TABLE_NAME),
            'carrier_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(Carrier::CARRIER_LABELS_TABLE_NAME, 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Shipping Rules Carrier Label'
        );
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'digidirect_extendedshippingrates_methods_label'
         */
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Carrier::METHOD_LABELS_TABLE_NAME)
        )->addColumn(
            'label_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Label Id'
        )->addColumn(
            'method_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Method Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(
            'label',
            Table::TYPE_TEXT,
            255,
            [],
            'Label'
        )->addIndex(
            $setup->getIdxName(
                Carrier::METHOD_LABELS_TABLE_NAME,
                ['method_id', 'store_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['method_id', 'store_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->addIndex(
            $setup->getIdxName(Carrier::METHOD_LABELS_TABLE_NAME, ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName(Carrier::METHOD_LABELS_TABLE_NAME, 'method_id', Carrier::METHOD_TABLE_NAME, 'entity_id'),
            'method_id',
            $setup->getTable(Carrier::METHOD_TABLE_NAME),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(Carrier::METHOD_LABELS_TABLE_NAME, 'store_id', 'store', 'store_id'),
            'store_id',
            $setup->getTable('store'),
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Shipping Rules Method Label'
        );
        $setup->getConnection()->createTable($table);
    }

    /**
     * Add method rates table
     *
     * @param SchemaSetupInterface $setup
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function addMethodRatesTable($setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(Carrier::RATE_TABLE_NAME))
            ->addColumn(
                'rate_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'method_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Corresponding Method ID'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Priority'
            )
            ->addColumn(
                'active',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'rate_method_price',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => '0'],
                'Rate price calculation type'
            )
            ->addColumn(
                'title',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Rate Title'
            )
            ->addColumn(
                'country_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Country'
            )
            ->addColumn(
                'region',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'State/Province'
            )
            ->addColumn(
                'region_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true, 'default' => null],
                'State/Province'
            )
            ->addColumn(
                'zip_from',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Zip/Postal Code From'
            )
            ->addColumn(
                'zip_to',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => null],
                'Zip/Postal Code To'
            )
            ->addColumn(
                'price_from',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price From'
            )
            ->addColumn(
                'price_to',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price To'
            )
            ->addColumn(
                'qty_from',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Qty From'
            )
            ->addColumn(
                'qty_to',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Qty To'
            )
            ->addColumn(
                'weight_from',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Weight From'
            )
            ->addColumn(
                'weight_to',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Weight To'
            )
            ->addColumn(
                'price',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price'
            )
            ->addColumn(
                'price_per_product',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price Per Product'
            )
            ->addColumn(
                'price_per_item',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price Per Item'
            )
            ->addColumn(
                'price_percent_per_product',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price Percent Per Product'
            )
            ->addColumn(
                'price_percent_per_item',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price Percent Per Item'
            )
            ->addColumn(
                'item_price_percent',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Item Price Percent'
            )
            ->addColumn(
                'price_per_weight',
                Table::TYPE_DECIMAL,
                '12,2',
                [],
                'Price Per One Unit Of Weight'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex(
                $setup->getIdxName(
                    Carrier::RATE_TABLE_NAME,
                    ['rate_id', 'method_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['rate_id', 'method_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $setup->getIdxName(Carrier::RATE_TABLE_NAME, ['rate_id']),
                ['rate_id']
            )
            ->addIndex(
                $setup->getIdxName(Carrier::RATE_TABLE_NAME, ['method_id']),
                ['method_id']
            )
            ->addForeignKey(
                $setup->getFkName(
                    Carrier::RATE_TABLE_NAME,
                    'method_id',
                    Carrier::METHOD_TABLE_NAME,
                    'entity_id'
                ),
                'method_id',
                $setup->getTable(Carrier::METHOD_TABLE_NAME),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Method Rates Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * Adds table for the zones
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addZonesTable($setup)
    {
        $table = $setup->getConnection()
            ->newTable($setup->getTable(Zone::ZONE_TABLE_NAME))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Priority'
            )
            ->addColumn(
                'is_active',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => '0'],
                'Is Active'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                [],
                'Name'
            )
            ->addColumn(
                'description',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Description'
            )
            ->addColumn(
                'conditions_serialized',
                Table::TYPE_TEXT,
                '2M',
                [],
                'Conditions Serialized'
            )
            ->addColumn(
                'default_shipping_method',
                Table::TYPE_TEXT,
                255,
                [],
                'Default Shipping Method'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Update Time'
            )
            ->addIndex(
                $setup->getIdxName(Zone::ZONE_TABLE_NAME, ['entity_id']),
                ['entity_id']
            )
            ->setComment('Zones Table');
        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addZonesStoreViewTable($setup)
    {
        $storeTable = $setup->getTable('store');
        $zonesStoresTable = $setup->getTable(Zone::ZONE_STORE_TABLE_NAME);

        /**
         * Create table 'digidirect_extendedshippingrates_zone_store' if not exists. This table will be used instead of
         * column store_ids of main shipping zones table
         */
        $table = $setup->getConnection()->newTable(
            $zonesStoresTable
        )->addColumn(
            'zone_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Zone Id'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Store View Id'
        )->addIndex(
            $setup->getIdxName(Zone::ZONE_STORE_TABLE_NAME, ['store_id']),
            ['store_id']
        )->addForeignKey(
            $setup->getFkName(Zone::ZONE_STORE_TABLE_NAME, 'zone_id', Zone::ZONE_TABLE_NAME, 'entity_id'),
            'zone_id',
            $setup->getTable(Zone::ZONE_TABLE_NAME),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(Zone::ZONE_STORE_TABLE_NAME, 'store_id', 'store', 'store_id'),
            'store_id',
            $storeTable,
            'store_id',
            Table::ACTION_CASCADE
        )->setComment(
            'Digidirect Shipping Zones To Stores Relations'
        );

        $setup->getConnection()->createTable($table);
    }
}
