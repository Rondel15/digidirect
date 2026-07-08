<?php
namespace Digidirect\ExtendedShippingRates\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Digidirect\ExtendedShippingRates\Model\Carrier;
use Digidirect\ExtendedShippingRates\Model\Rule;

class Uninstall implements UninstallInterface
{
    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $connection->dropTable($connection->getTableName(Rule::CUSTOMER_GROUP_TABLE_NAME));
        $connection->dropTable($connection->getTableName(Rule::STORE_TABLE_NAME));
        $connection->dropTable($connection->getTableName(Rule::RULE_TABLE_NAME));
        $connection->dropTable($connection->getTableName(Carrier::CARRIER_TABLE_NAME));
        $connection->dropTable($connection->getTableName(Carrier::METHOD_TABLE_NAME));
        $connection->dropTable($connection->getTableName(Carrier::METHOD_LABELS_TABLE_NAME));
        $connection->dropTable($connection->getTableName(Carrier::CARRIER_LABELS_TABLE_NAME));

        $setup->endSetup();
    }
}
