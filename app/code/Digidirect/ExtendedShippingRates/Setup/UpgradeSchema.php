<?php

namespace Digidirect\ExtendedShippingRates\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier;
use Digidirect\ExtendedShippingRates\Model\Carrier\Method;
use Magento\Framework\DB\FieldDataConverterFactory;
use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\ExtendedShippingRates\Api\Data\RuleInterface;
use Digidirect\ExtendedShippingRates\Model\Zone;
use Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleZoneInterface;
use Digidirect\ExtendedShippingRates\Api\Data\PostProcessingInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface;
use Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeCodeInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 * @package Digidirect\ExtendedShippingRates\Setup
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const CODE_MAX_LENGTH = 18;

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var FieldDataConverterFactory
     */
    protected $fieldDataConverterFactory;

    /**
     * UpgradeSchema constructor.
     * @param FieldDataConverterFactory $fieldDataConverterFactory
     */
    public function __construct(FieldDataConverterFactory $fieldDataConverterFactory)
    {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->context = $context;
        $this->setup = $setup;
        $this->connection = $setup->getConnection();

        $this->setup->startSetup();

        if ($this->compareVersions('1.0.1')) {
            $this->modifyVarcharColumnLength(Carrier::CARRIER_TABLE_NAME, Carrier::CARRIER_CODE, self::CODE_MAX_LENGTH);
            $this->modifyVarcharColumnLength(Carrier::METHOD_TABLE_NAME, Method::CODE, self::CODE_MAX_LENGTH);
        }

        if ($this->compareVersions('1.0.2')) {
            $this->convertSerializedData([
                RuleInterface::AMOUNT, RuleInterface::ACTION_TYPE, RuleInterface::SHIPPING_METHODS,
                RuleInterface::DISABLED_SHIPPING_METHODS, RuleInterface::ENABLED_SHIPPING_METHODS
            ]);
        }

        if ($this->compareVersions('1.0.3')) {
            $this->convertSerializedData([RuleInterface::ACTIONS_SERIALIZED, RuleInterface::CONDITIONS_SERIALIZED]);
        }

        if ($this->compareVersions('1.0.4')) {
            $this->addActionTypeOptionColumn($setup);
        }

        if ($this->compareVersions('1.0.5')) {
            $this->addRuleZoneColumns($setup);
        }

        if ($this->compareVersions('1.0.6')) {
            $this->addSimpleAttributeSetColumn($setup);
        }

        if ($this->compareVersions('1.0.7')) {
            $this->addZoneIdColumn($setup);
        }

        if ($this->compareVersions('1.0.8')) {
            $this->addRulePostProcessingColumn($setup);
            $this->modifyVarcharColumnLength(
                $this->setup->getTable(Zone::ZONE_TABLE_NAME),
                ZoneInterface::ZONE_ID,
                255
            );
            $this->addUniqueIndexToZoneId($setup);
        }

        if ($this->compareVersions('1.0.9')) {
            $setup->getConnection()->dropColumn(
                $this->setup->getTable(Rule::RULE_TABLE_NAME),
                RuleZoneInterface::USE_ZONES_FROM_STATE
            );
        }

        if ($this->compareVersions('1.1.0')) {
            $this->addMethodAlternativeTitleColumn($setup);
        }

        if ($this->compareVersions('1.1.1')) {
            $this->addMethodAlternativeCodeColumn($setup);
        }

        if ($this->compareVersions('1.1.2')) {
            $this->modifyZoneRegionIdColumn();
        }

        if ($this->compareVersions('1.1.3')) {
            $this->addRuleAlternativeTitleColumns($setup);
        }

        if ($this->compareVersions('1.1.4')) {
            $this->addRuleAlternativeCodeColumns($setup);
        }

        if ($this->compareVersions('1.1.5')) {
            $this->addShiftShippingAvailabilityCheckColumn($setup);
        }

        $this->setup->endSetup();
    }

    /**
     * Modify varchar type column length
     *
     * @param string $tableName
     * @param string $columnName
     * @param int $length
     * @return void
     */
    public function modifyVarcharColumnLength($tableName, $columnName, $length = 64)
    {
        if ($this->connection->isTableExists($tableName)) {
            $this->connection->modifyColumn(
                $this->connection->getTableName($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => $length
                ]
            );
        }
    }

    /**
     * Convert serialized fields to json
     * @param array $serializedFields
     * @return void
     */
    public function convertSerializedData($serializedFields)
    {
        /**
         * @var \Magento\Framework\DB\FieldDataConverter $fieldDataConverter
         */
        $fieldDataConverter = $this->fieldDataConverterFactory->create(
            \Digidirect\ExtendedShippingRates\Setup\SerializedToJsonDataConverter::class
        );
        foreach ($serializedFields as $serializedField) {
            $fieldDataConverter->convert(
                $this->connection,
                $this->setup->getTable(Rule::RULE_TABLE_NAME),
                RuleInterface::RULE_ID,
                $serializedField
            );
        }
    }

    /**
     * Compare versions
     *
     * @param string $new
     * @return bool
     */
    protected function compareVersions($new)
    {
        return version_compare($this->context->getVersion(), $new, '<');
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addActionTypeOptionColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            'action_type_option',
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Show or Hide Shipping Method'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addSimpleAttributeSetColumn($setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            ZoneInterface::ATTRIBUTE_SET,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Attribute Set'
            ]
        );
        $connection->addColumn(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            ZoneInterface::COUNTRY_ID,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 4,
                'comment' => 'Country'
            ]
        );
        $connection->addColumn(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            ZoneInterface::REGION_ID,
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'comment' => 'State'
            ]
        );
        $connection->addColumn(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            ZoneInterface::POSTCODE,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Postcode'
            ]
        );
        $connection->addIndex(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            $setup->getIdxName($this->setup->getTable(Zone::ZONE_TABLE_NAME), [ZoneInterface::COUNTRY_ID]),
            [ZoneInterface::COUNTRY_ID]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addZoneIdColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            ZoneInterface::ZONE_ID,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'comment' => 'Zone Id'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addUniqueIndexToZoneId(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addIndex(
            $this->setup->getTable(Zone::ZONE_TABLE_NAME),
            $setup->getIdxName(
                $this->setup->getTable(Zone::ZONE_TABLE_NAME),
                [ZoneInterface::ZONE_ID],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [ZoneInterface::ZONE_ID],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addRuleZoneColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleZoneInterface::ZONE_COUNTRY,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'comment' => 'Zone Country'
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleZoneInterface::ZONE_STATE,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 255,
                'comment' => 'Zone State'
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleZoneInterface::ZONE_STATE_ID,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Zone State Id'
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleZoneInterface::USE_ZONES_FROM_STATE,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Use Zones From State'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addRulePostProcessingColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            PostProcessingInterface::POST_PROCESSING,
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Post Processing Flag'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addMethodAlternativeTitleColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Carrier::METHOD_TABLE_NAME),
            Method::ALTERNATIVE_TITLE,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 64,
                'comment' => 'Alternative Title'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addRuleAlternativeTitleColumns($setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleAlternativeTitleInterface::USED_ALT_TITLE_SHIPPING_METHODS,
            [
                'type' => Table::TYPE_TEXT,
                'size' => '64k',
                'comment' => 'Used Alternative Title Shipping Methods'
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleAlternativeTitleInterface::ACTION_OFFSET_BEGINS,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Action Offset Begins'
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleAlternativeTitleInterface::ACTION_OFFSET_ENDS,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Action Offset Ends'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addMethodAlternativeCodeColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Carrier::METHOD_TABLE_NAME),
            Method::ALTERNATIVE_CODE,
            [
                'type' => Table::TYPE_TEXT,
                'size' => 64,
                'comment' => 'Alternative Code'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addRuleAlternativeCodeColumns($setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleAlternativeCodeInterface::USED_ALT_CODE_SHIPPING_METHODS,
            [
                'type' => Table::TYPE_TEXT,
                'size' => '64k',
                'comment' => 'Use Shipping Method Alternative Code '
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleAlternativeCodeInterface::ALT_CODE_ACTION_OFFSET_BEGINS,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Alternative Code Action Offset Begins'
            ]
        );

        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            RuleAlternativeCodeInterface::ALT_CODE_ACTION_OFFSET_ENDS,
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Alternative Code Action Offset Ends'
            ]
        );
    }

    /**
     * @return void
     */
    protected function modifyZoneRegionIdColumn()
    {
        if ($this->connection->isTableExists(Zone::ZONE_TABLE_NAME)) {
            $this->connection->modifyColumn(
                $this->connection->getTableName(Zone::ZONE_TABLE_NAME),
                ZoneInterface::REGION_ID,
                [
                    'type' => Table::TYPE_INTEGER,
                    'comment' => 'State'
                ]
            );
            $this->connection->addIndex(
                $this->setup->getTable(Zone::ZONE_TABLE_NAME),
                $this->setup->getIdxName($this->setup->getTable(Zone::ZONE_TABLE_NAME), [ZoneInterface::REGION_ID]),
                [ZoneInterface::REGION_ID]
            );
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addShiftShippingAvailabilityCheckColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $this->setup->getTable(Rule::RULE_TABLE_NAME),
            Rule::SHIFT_SHIPPING_AVAILABILITY_CHECK,
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'unsigned' => true,
                'comment' => 'Shift shipping availability check',
            ]
        );
    }
}
