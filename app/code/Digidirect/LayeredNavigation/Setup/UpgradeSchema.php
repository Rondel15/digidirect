<?php
namespace Digidirect\LayeredNavigation\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $tableName = $setup->getTable('digidirect_layerednavigation_filter_setting');
            $connection = $setup->getConnection();
            if ($connection->isTableExists($tableName)) {
                $connection->dropColumn($tableName, 'is_seo_significant');
                $connection->addColumn(
                    $tableName,
                    'use_and_logic',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Use AND Logic For Multiple Selections'
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $tableName = $setup->getTable('digidirect_layerednavigation_filter_setting');
            $connection = $setup->getConnection();
            if ($connection->isTableExists($tableName)) {
                $connection->addColumn(
                    $tableName,
                    'show_more_enabled',
                    [
                        'type' => Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Is Show More link enabled',
                    ]
                );

                $connection->addColumn(
                    $tableName,
                    'show_more_count',
                    [
                        'type' => Table::TYPE_INTEGER,
                        'nullable' => false,
                        'default' => 5,
                        'comment' => 'Count of Options per Filter',
                    ]
                );
            }
        }

        $setup->endSetup();
    }
}
