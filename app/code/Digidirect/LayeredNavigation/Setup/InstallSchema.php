<?php
namespace Digidirect\LayeredNavigation\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('digidirect_layerednavigation_filter_setting');
        $table = $installer->getConnection()
            ->newTable($tableName)
            ->addColumn(
                'setting_id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Setting ID'
            )
            ->addColumn(
                'filter_code',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Filter Code'
            )
            ->addColumn(
                'is_multiselect',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Is MultiSelect'
            )
            ->addColumn(
                'display_mode',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Display Mode'
            )
            ->addColumn(
                'is_seo_significant',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'Is SEO Significant'
            )
            ->addColumn(
                'slider_step',
                Table::TYPE_DECIMAL,
                12.4,
                ['nullable' => false, 'default' => 1],
                'Slider Step'
            )
            ->addColumn(
                'units_label_use_currency_symbol',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true],
                'Is Units label used currency symbol'
            )
            ->addColumn(
                'units_label',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Units label'
            )
            ->addColumn(
                'index_mode',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Robots Index Mode'
            )
            ->addColumn(
                'follow_mode',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Robots Follow Mode'
            )
            ->addColumn(
                'hide_one_option',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'default' => 0],
                'Hide filter when only one option available'
            );

        $installer->getConnection()->createTable($table);
        $installer->getConnection()->query('ALTER TABLE `' . $tableName . '` ADD UNIQUE(`filter_code`)');
        $installer->endSetup();
    }
}
