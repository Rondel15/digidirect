<?php
namespace Digidirect\ExtendedCatalogPriceRule\Setup;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Digidirect\ExtendedCatalogPriceRule\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addDigidirectExtendedCatalogPriceRuleTable($setup);
        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function addDigidirectExtendedCatalogPriceRuleTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable(ExtendedCatalogRule::EXTENDED_CATALOG_RULE_TABLE);
        if ($setup->getConnection()->isTableExists($tableName)) {
            return;
        }

        $table = $setup->getConnection()->newTable(
            $tableName
        )->addColumn(
            ExtendedCatalogRuleInterface::ID,
            Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        )->addColumn(
            ExtendedCatalogRuleInterface::RULE_ID,
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Magento Catalog Price Rule ID'
        )->addColumn(
            RuleDisplayMessageInterface::PLP_LABEL,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Label on PLP'
        )->addColumn(
            RuleDisplayMessageInterface::PDP_DESCRIPTION,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'PDP Description'
        )->addColumn(
            RuleDisplayMessageInterface::URL_PROMOTION,
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Promotional URL'
        )->addForeignKey(
            $setup->getFkName(
                $tableName,
                ExtendedCatalogRuleInterface::RULE_ID,
                'sequence_catalogrule',
                'sequence_value'
            ),
            ExtendedCatalogRuleInterface::RULE_ID,
            $setup->getTable('sequence_catalogrule'),
            'sequence_value',
            Table::ACTION_CASCADE
        );
        $setup->getConnection()->createTable($table);
    }
}
