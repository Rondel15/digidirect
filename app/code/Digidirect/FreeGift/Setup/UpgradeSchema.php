<?php

namespace Digidirect\FreeGift\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Digidirect\FreeGift\Model\Cart\Item;

/**
 * Upgrade the module DB scheme
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
        $this->_context = $context;
        $this->_setup = $setup;
        $this->_connection = $setup->getConnection();

        $this->_setup->startSetup();

        if ($this->_compareVersions('1.1.0')) {
            $this->_addEnableOnPdpField();
        }

        if ($this->_compareVersions('1.1.1')) {
            $this->upgrade111();
        }

        if ($this->_compareVersions('1.1.2')) {
            $this->_addIsHiddenForCustomerField();
        }

        if ($this->_compareVersions('1.1.3')) {
            $this->_addMessagesFields();
        }

        if ($this->_compareVersions('1.1.4')) {
            $this->_addRuleIdField();
        }

        $this->_setup->endSetup();
    }

    /**
     * Add new fields to digidirect_freegift_rule table: enable_on_pdp
     *
     * @return void
     */
    protected function _addEnableOnPdpField()
    {
        $tableName = 'digidirect_freegift_rule';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add "Enable Free Gift Block On Product Detail" column
        $columnName = 'enable_on_pdp';
        $this->_connection->addColumn(
            $this->_setup->getTable($tableName),
            $columnName,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Enable Free Gift Block On Product Detail',
            ]
        );
    }

    /**
     * Compare versions
     *
     * @param string $new
     * @return bool
     */
    protected function _compareVersions($new)
    {
        return version_compare($this->_context->getVersion(), $new, '<');
    }

    /**
     * Check if table is exists
     *
     * @param string $tableName
     * @return bool
     */
    protected function _isTableExists($tableName)
    {
        return $this->_connection->isTableExists($tableName);
    }

    /**
     * Add "show_desc_on_pdp" flag
     *
     * @return void
     */
    protected function addShowDescriptionFlag()
    {
        $tableName = 'digidirect_freegift_rule';
        if (!$this->_isTableExists($tableName)) {
            return;
        }
        $columnName = 'show_desc_on_pdp';
        if ($this->_connection->tableColumnExists($tableName, $columnName)) {
            return;
        }

        // Add "Enable Free Gift Description" column
        $this->_connection->addColumn(
            $this->_setup->getTable($tableName),
            $columnName,
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'default' => '0',
                'comment' => 'Show description on product details page',
            ]
        );
    }

    /**
     * Add Free Gift "Description" column
     *
     * @return void
     */
    protected function addDescriptionLabelField()
    {
        $tableName = 'digidirect_freegift_rule';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        // Add Free Gift "Description" column
        $columnName = 'description_label';

        if ($this->_connection->tableColumnExists($tableName, $columnName)) {
            return;
        }

        $this->_connection->addColumn(
            $this->_setup->getTable($tableName),
            $columnName,
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Free gift description',
            ]
        );
    }

    /**
     * Upgrade to 1.1.1 version
     *
     * @return void
     */
    protected function upgrade111()
    {
        $this->addDescriptionLabelField();
        $this->addShowDescriptionFlag();
    }

    /**
     * @return void
     */
    protected function _addIsHiddenForCustomerField()
    {
        $adapter = $this->_connection;
        $tableName = $this->_setup->getTable('digidirect_freegift_rule');
        if ($adapter->isTableExists($tableName)) {
            $columnName = 'is_hidden_for_customer';
            if (!$adapter->tableColumnExists($tableName, $columnName)) {
                $adapter->addColumn(
                    $tableName,
                    $columnName,
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => false,
                        'default' => 0,
                        'comment' => 'Is hidden for customer',
                    ]
                );
            }
        }
    }

    /**
     * @return void
     */
    protected function _addMessagesFields()
    {
        $tableName = 'digidirect_freegift_rule';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        $columnNames = [
            'cart_message',
            'prefix'
        ];
        $createColumns = [];
        foreach ($columnNames as $columnName) {
            if (!$this->_connection->tableColumnExists($tableName, $columnName)) {
                $createColumns[] = $columnName;
            }
        }

        foreach ($createColumns as $columnName) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => $columnName,
                ]
            );
        }
    }

    /**
     * @return void
     */
    protected function _addRuleIdField()
    {
        $tableName = 'quote_item';
        if (!$this->_isTableExists($tableName)) {
            return;
        }

        $columnName = Item::FREE_GIFT_ADDED_BY_RULE_ID;
        if (!$this->_connection->tableColumnExists($tableName, $columnName)) {
            $this->_connection->addColumn(
                $this->_setup->getTable($tableName),
                $columnName,
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => $columnName,
                ]
            );
        }
    }
}
