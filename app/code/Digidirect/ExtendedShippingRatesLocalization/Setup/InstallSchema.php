<?php
namespace Digidirect\ExtendedShippingRatesLocalization\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Digidirect\ExtendedShippingRates\Model\Rule;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->addUseStateTimezoneColumn($setup);
        $this->addUnavailableOnHolidaysColumn($setup);

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addUseStateTimezoneColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(Rule::RULE_TABLE_NAME),
            'use_state_timezone',
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Use State Timezone'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addUnavailableOnHolidaysColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable(Rule::RULE_TABLE_NAME),
            'unavailable_on_holidays',
            [
                'type' => Table::TYPE_SMALLINT,
                'comment' => 'Unavailable On Holidays'
            ]
        );
    }
}
