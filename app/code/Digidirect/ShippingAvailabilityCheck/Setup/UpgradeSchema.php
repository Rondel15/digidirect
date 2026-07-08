<?php

namespace Digidirect\ShippingAvailabilityCheck\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Digidirect\ShippingAvailabilityCheck\Api\Data\Quote\CartInterface;

/**
 * Class UpgradeSchema
 * @package Digidirect\ShippingAvailabilityCheck\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ModuleContextInterface
     */
    protected $_context;

    /**
     * @var SchemaSetupInterface
     */
    protected $_setup;

    /**
     * @var AdapterInterface
     */
    protected $_connection;

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addColumnToQuoteTable($installer);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    protected function addColumnToQuoteTable(SchemaSetupInterface $setup)
    {
        $quoteTable = $setup->getTable('quote');

        $connection = $setup->getConnection();

        $connection->addColumn(
            $quoteTable,
            CartInterface::SHIPPING_AVAILABILITY_CHECK_HASH,
            [
                'type' => Table::TYPE_TEXT,
                'comment' => 'Shipping Availability Check Hash',
            ]
        );

        return $this;
    }
}
