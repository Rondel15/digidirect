<?php

namespace Digidirect\CheckoutFields\Setup;

use Digidirect\CheckoutFields\Api\Data\PdpFieldValueInterface;
use Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class UpgradeSchema
 * @package Digidirect\CheckoutFields\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    const ORDER_TABLE = 'digidirect_checkout_fields_order_field_value';
    const QUOTE_TABLE = 'digidirect_checkout_fields_quote_field_value';

    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->context = $context;
        $this->setup = $setup;
        $this->connection = $setup->getConnection();

        $this->setup->startSetup();

        if ($this->compareVersions('1.0.1')) {
            $this->addFieldIdColumn(self::QUOTE_TABLE);
            $this->addFieldIdColumn(self::ORDER_TABLE);
        }

        if ($this->compareVersions('1.0.2')) {
            $this->setLengthLimitToFieldId();
            $this->addUniqueIndexes();
        }

        if ($this->compareVersions('1.0.3')) {
            $this->addTableForPDPFiledValue();
        }

        $this->setup->endSetup();
    }

    /**
     * Add additional column to table
     * @param string $tableName
     * @return void
     */
    public function addFieldIdColumn($tableName)
    {
        $columnName = 'field_id';
        if ($this->isTableExists($tableName) && !$this->isColumnExists($tableName, $columnName)) {
            $this->connection->addColumn(
                $tableName,
                $columnName,
                [
                    'default' => null,
                    'type' => Table::TYPE_TEXT,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Digidirect order field id',
                ]
            );
        }
    }

    /**
     * @return void
     */
    public function setLengthLimitToFieldId()
    {
        $tableName = $this->connection->getTableName(self::QUOTE_TABLE);
        $this->connection->modifyColumn($tableName, 'field_id', [
            'default' => null,
            'type' => Table::TYPE_TEXT,
            'length' => 64,
            'nullable' => true,
            'comment' => 'Digidirect quote field id',
        ]);

        $tableName = $this->connection->getTableName(self::ORDER_TABLE);
        $this->connection->modifyColumn($tableName, 'field_id', [
            'default' => null,
            'type' => Table::TYPE_TEXT,
            'length' => 64,
            'nullable' => true,
            'comment' => 'Digidirect order field id',
        ]);
    }

    /**
     * @return void
     */
    public function addUniqueIndexes()
    {
        $tableName = $this->connection->getTableName(self::QUOTE_TABLE);
        $fields = ['quote_id', 'field_id'];
        $indexType = AdapterInterface::INDEX_TYPE_UNIQUE;
        $this->connection->addIndex(
            $tableName,
            $this->connection->getIndexName($tableName, $fields, $indexType),
            $fields,
            $indexType
        );

        $tableName = $this->connection->getTableName(self::ORDER_TABLE);
        $fields = ['order_id', 'field_id'];
        $indexType = AdapterInterface::INDEX_TYPE_UNIQUE;
        $this->connection->addIndex(
            $tableName,
            $this->connection->getIndexName($tableName, $fields, $indexType),
            $fields,
            $indexType
        );
    }

    /**
     * The table will serve as a buffer for user-filled custom fields values on PDP page,
     * which give an opportunity to pre-set values for PDP-fields on the checkout page.
     *
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function addTableForPDPFiledValue()
    {
        $tableName = $this->connection->getTableName(PdpFieldValue::PDP_VALUES_TABLE);
        if (!$this->isTableExists($tableName)) {
            $table = $this->connection->newTable(
                $tableName
            )->addColumn(
                PdpFieldValueInterface::ENTITY_ID,
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )->addColumn(
                PdpFieldValueInterface::QUOTE_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Quote ID'
            )->addColumn(
                PdpFieldValueInterface::PRODUCT_ID,
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false, 'unsigned' => true],
                'Product ID'
            )->addColumn(
                PdpFieldValueInterface::FIELD_CODE,
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Field Code'
            )->addColumn(
                PdpFieldValueInterface::FIELD_VALUE,
                Table::TYPE_TEXT,
                null,
                [],
                'Field Value'
            )->addForeignKey(
                $this->setup->getFkName(
                    $tableName,
                    PdpFieldValueInterface::QUOTE_ID,
                    'quote',
                    'entity_id'
                ),
                PdpFieldValueInterface::QUOTE_ID,
                $this->setup->getTable('quote'),
                'entity_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $this->setup->getFkName(
                    $tableName,
                    PdpFieldValueInterface::PRODUCT_ID,
                    'catalog_product_entity',
                    'entity_id'
                ),
                PdpFieldValueInterface::PRODUCT_ID,
                $this->setup->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )->addIndex(
                $this->setup->getIdxName(
                    $tableName,
                    [
                        PdpFieldValueInterface::QUOTE_ID,
                        PdpFieldValueInterface::PRODUCT_ID,
                        PdpFieldValueInterface::FIELD_CODE,
                    ],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                    PdpFieldValueInterface::QUOTE_ID,
                    PdpFieldValueInterface::PRODUCT_ID,
                    PdpFieldValueInterface::FIELD_CODE,
                ],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
                ->setComment('Digidirect CheckoutFields Table for Values from PDP');
            $this->connection->createTable($table);
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
     * Check if column exists
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    protected function isColumnExists($tableName, $columnName)
    {
        return $this->connection->tableColumnExists($tableName, $columnName);
    }

    /**
     * Check if column exists
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    protected function deleteColumn($tableName, $columnName)
    {
        return $this->connection->dropColumn($tableName, $columnName);
    }

    /**
     * Check if table exists
     *
     * @param string $tableName
     * @return bool
     */
    protected function isTableExists($tableName)
    {
        return $this->connection->isTableExists($tableName);
    }
}
