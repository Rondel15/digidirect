<?php
namespace Digidirect\ExtendedCartPriceRules\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addPaymentMethodLimitColumn($setup);
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $this->addDigidirectExtendedCartPriceRuleTable($setup);
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $this->addExtendPaymentMethodLimitColumns($setup);
        }

        $setup->endSetup();
    }

    /**
     * The 'payment_method_limit' column added to the 'salesrule' table.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    protected function addPaymentMethodLimitColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('salesrule'),
            'payment_method_limit',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Payment Method Limit',
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return  void
     */
    protected function addDigidirectExtendedCartPriceRuleTable(SchemaSetupInterface $setup)
    {
        $tableName = $setup->getTable('digidirect_extended_cart_price_rule');
        if (!$setup->getConnection()->isTableExists($tableName)) {
            $table = $setup->getConnection()->newTable(
                $tableName
            )->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'id'
            )->addColumn(
                'message',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'message'
            )->addForeignKey(
                $setup->getFkName(
                    $tableName,
                    'rule_id',
                    'sequence_salesrule',
                    'sequence_value'
                ),
                'rule_id',
                $setup->getTable('sequence_salesrule'),
                'sequence_value',
                Table::ACTION_CASCADE,
                Table::ACTION_CASCADE
            );
            $setup->getConnection()->createTable($table);
        }
    }

    /**
     * The 'enable_unavailable_payment_methods' and 'message_for_unavailable_payment_method' columns
     * added to the 'salesrule' table.
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addExtendPaymentMethodLimitColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('salesrule'),
            'enable_unavailable_payment_methods',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'default'   => 0,
                'nullable'  => false,
                'comment'   => 'Enable Unavailable Payment Methods',
            ]
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('salesrule'),
            'message_for_unavailable_payment_method',
            [
                'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'default'   => '',
                'nullable'  => true,
                'comment'   => 'Message to Display for Unavailable Payment Methods',
            ]
        );
    }
}
