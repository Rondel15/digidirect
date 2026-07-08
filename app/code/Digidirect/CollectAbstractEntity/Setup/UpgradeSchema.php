<?php
namespace Digidirect\CollectAbstractEntity\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Digidirect\CollectAbstractEntity\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->addEmailSentColumnToShipmentTrackTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * Add "email_sent" column to "sales_shipment_track" table
     *
     * @param SchemaSetupInterface $setup
     */
    public function addEmailSentColumnToShipmentTrackTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()
            ->addColumn(
                $setup->getTable('sales_shipment_track'),
                \Digidirect\CollectAbstractEntity\Model\ResourceModel\Order\Shipment\Track::EMAIL_SENT,
                [
                    'type'     => Table::TYPE_BOOLEAN,
                    'default'  => false,
                    'nullable' => false,
                    'comment'  => 'Email Sent'
                ]
            );
    }
}
