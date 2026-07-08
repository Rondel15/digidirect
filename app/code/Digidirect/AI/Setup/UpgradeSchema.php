<?php

namespace Digidirect\AI\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Digidirect\AI\Api\Data\ScheduleInterface;
use Digidirect\AI\Model\ResourceModel\Logger\Logger as LoggerResource;

/**
 * Class UpgradeSchema
 *
 * @package Digidirect\AI\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var ModuleContextInterface
     */
    protected $context;

    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * upgrade
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        /**
         * @var $adapter \Magento\Framework\DB\Adapter\Pdo\Mysql
         */
        $this->context = $context;
        $this->setup = $setup;
        $setup->startSetup();

        $adapter = $setup->getConnection();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('digidirect_ai_logs'),
                    'process_code',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => false,
                        'comment' => 'Process code',
                        'after' => 'sync_type',
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.0.2') < 0) {
            $processSchedule = $setup->getConnection()->newTable(
                $setup->getTable('digidirect_ai_schedule_run')
            )->addColumn(
                'id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Record ID'
            )->addColumn(
                'process_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Process code'
            )->addColumn(
                'run_options',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Process Run Options'
            )->addColumn(
                'created',
                Table::TYPE_DATETIME,
                null,
                [],
                'Record Creation Time'
            )->addColumn(
                'comment',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Comment'
            );

            $setup->getConnection()->createTable($processSchedule);
        }

        if (version_compare($context->getVersion(), '2.0.3') < 0) {
            $queueTable = $setup->getConnection()->newTable(
                $setup->getTable('digidirect_ai_queue')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Record ID'
            )->addColumn(
                'process_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Process Code'
            )->addColumn(
                'initiator',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Initiator'
            )->addColumn(
                'process_data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Process Data'
            )->addColumn(
                'state',
                Table::TYPE_SMALLINT,
                null,
                ['default' => \Digidirect\AI\Model\Engine\Queue\State::STATE_PENDING, 'nullable' => false],
                'Run State'
            )->addColumn(
                'status',
                Table::TYPE_SMALLINT,
                null,
                ['default' => \Digidirect\AI\Model\Engine\Queue\Status::STATUS_PENDING, 'nullable' => false],
                'Status'
            )->addColumn(
                'sequence',
                Table::TYPE_BIGINT,
                null,
                ['default' => 0, 'nullable' => false],
                'Run Sequence'
            )->addColumn(
                'run_number',
                Table::TYPE_INTEGER,
                null,
                ['default' => 0, 'nullable' => false],
                'Run Numbers'
            )->addColumn(
                'last_run_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Last Run Process Date Time'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Creation Time'
            );
            $setup->getConnection()->createTable($queueTable);

            $queueLogTable = $setup->getConnection()->newTable(
                $setup->getTable('digidirect_ai_queue_log')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Record ID'
            )->addColumn(
                'log_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Relation to log'
            )->addColumn(
                'queue_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Relation to queue'
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('digidirect_ai_queue_log'),
                    'log_id',
                    $setup->getTable('digidirect_ai_logs'),
                    'id'
                ),
                'log_id',
                $setup->getTable('digidirect_ai_logs'),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('digidirect_ai_queue_log'),
                    'queue_id',
                    $setup->getTable('digidirect_ai_queue'),
                    'id'
                ),
                'queue_id',
                $setup->getTable('digidirect_ai_queue'),
                'id',
                Table::ACTION_CASCADE
            );
            $setup->getConnection()->createTable($queueLogTable);

            $logConnectorTable = $setup->getConnection()->newTable(
                $setup->getTable('digidirect_ai_logs_connector_data')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Record ID'
            )->addColumn(
                'log_id',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Relation to log'
            )->addColumn(
                'url',
                Table::TYPE_TEXT,
                512,
                ['nullable' => true],
                'Request url'
            )->addColumn(
                'method',
                Table::TYPE_TEXT,
                8,
                ['nullable' => true],
                'Request method'
            )->addColumn(
                'response_status',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => true],
                'Response status'
            )->addColumn(
                'request_file',
                Table::TYPE_TEXT,
                512,
                ['nullable' => true],
                'Request file path'
            )->addColumn(
                'response_file',
                Table::TYPE_TEXT,
                512,
                ['nullable' => true],
                'Response file path'
            )->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('digidirect_ai_logs_connector_data'),
                    'log_id',
                    $setup->getTable('digidirect_ai_logs'),
                    'id'
                ),
                'log_id',
                $setup->getTable('digidirect_ai_logs'),
                'id',
                Table::ACTION_CASCADE
            );
            $setup->getConnection()->createTable($logConnectorTable);
        }

        if (version_compare($context->getVersion(), '2.0.4') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable('digidirect_ai_queue'),
                    'is_retired',
                    [
                        'type' => Table::TYPE_BOOLEAN,
                        'nullable' => false,
                        'default' => false,
                        'comment' => 'Flag if record is retired',
                        'after' => 'state',
                    ]
                );
        }

        if (version_compare($context->getVersion(), '2.0.5') < 0) {
            $setup->getConnection()->addIndex(
                $setup->getTable('digidirect_ai_queue'),
                $setup->getIdxName(
                    $setup->getTable('digidirect_ai_queue'),
                    ['process_code'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['process_code'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('digidirect_ai_queue'),
                $setup->getIdxName(
                    $setup->getTable('digidirect_ai_queue'),
                    ['initiator'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['initiator'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('digidirect_ai_queue'),
                $setup->getIdxName($setup->getTable('digidirect_ai_queue'), ['status']),
                ['status']
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('digidirect_ai_queue'),
                $setup->getIdxName($setup->getTable('digidirect_ai_queue'), ['state']),
                ['state']
            );

            $setup->getConnection()->addIndex(
                $setup->getTable('digidirect_ai_queue'),
                $setup->getIdxName($setup->getTable('digidirect_ai_queue'), ['run_number']),
                ['run_number']
            );
        }
        if (version_compare($context->getVersion(), '2.0.6') < 0) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('digidirect_ai_queue_log'),
                'id',
                'entity_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true,
                    'comment' => 'Record ID',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.7') < 0) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('digidirect_ai_integrations'),
                'id',
                'id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.0.8') < 0) {
            $adapter->dropForeignKey($setup->getTable('digidirect_ai_queue_log'), $setup->getFkName(
                $setup->getTable('digidirect_ai_queue_log'),
                'log_id',
                $setup->getTable('digidirect_ai_logs'),
                'id'
            ));

            $adapter
                ->dropForeignKey($setup->getTable('digidirect_ai_logs_connector_data'), $setup->getFkName(
                    $setup->getTable('digidirect_ai_logs_connector_data'),
                    'log_id',
                    $setup->getTable('digidirect_ai_logs'),
                    'id'
                ));

            $adapter->changeColumn(
                $setup->getTable('digidirect_ai_logs_connector_data'),
                'log_id',
                'log_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Log Record Reference',
                ]
            );

            $adapter->changeColumn(
                $setup->getTable('digidirect_ai_queue_log'),
                'log_id',
                'log_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Log Record Reference',
                ]
            );

            $adapter->changeColumn(
                $setup->getTable('digidirect_ai_logs'),
                'id',
                'id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true,
                    'comment' => 'Record ID',
                ]
            );

            $adapter->changeColumn(
                $setup->getTable('digidirect_ai_logs_details'),
                'id',
                'id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true,
                    'comment' => 'Record ID',
                ]
            );

            $adapter->changeColumn(
                $setup->getTable('digidirect_ai_logs_details'),
                'log_id',
                'log_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Log Record Reference',
                ]
            );
            $adapter->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('digidirect_ai_queue_log'),
                    'log_id',
                    $setup->getTable('digidirect_ai_logs'),
                    'id'
                ),
                $setup->getTable('digidirect_ai_queue_log'),
                'log_id',
                $setup->getTable('digidirect_ai_logs'),
                'id',
                Table::ACTION_CASCADE
            );
            $adapter->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('digidirect_ai_logs_connector_data'),
                    'log_id',
                    $setup->getTable('digidirect_ai_logs'),
                    'id'
                ),
                $setup->getTable('digidirect_ai_logs_connector_data'),
                'log_id',
                $setup->getTable('digidirect_ai_logs'),
                'id',
                Table::ACTION_CASCADE
            );
        }

        if (version_compare($context->getVersion(), '2.0.9') < 0) {
            $mapperDataTable = $adapter->newTable(
                $setup->getTable('digidirect_ai_mapping_data')
            )->addColumn(
                'id',
                Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Record ID'
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Entity ID'
            )->addColumn(
                'mapper_code',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Mapper code'
            )->addColumn(
                'data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Mapper data'
            );

            $adapter->createTable($mapperDataTable);
        }

        if ($this->compareVersions('2.1.0')) {
            $adapter->addColumn(
                $setup->getTable('digidirect_ai_integrations'),
                'cron_time',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 63,
                    'nullable' => false,
                    'comment' => 'Cron Time',
                    'default' => '',
                ]
            );
        }

        if ($this->compareVersions('2.1.1')) {
            $this->addIntegrationRunDateTime();
        }

        if ($this->compareVersions('2.1.2')) {
            $adapter->addIndex(
                $setup->getTable('digidirect_ai_logs_details'),
                $setup->getIdxName(
                    $setup->getTable('digidirect_ai_logs_details'),
                    ['log_id'],
                    AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['log_id'],
                AdapterInterface::INDEX_TYPE_INDEX
            );
            $adapter->addForeignKey(
                $setup->getFkName(
                    $setup->getTable('digidirect_ai_logs_details'),
                    'log_id',
                    $setup->getTable('digidirect_ai_logs'),
                    'id'
                ),
                $setup->getTable('digidirect_ai_logs_details'),
                'log_id',
                $setup->getTable('digidirect_ai_logs'),
                'id',
                Table::ACTION_CASCADE
            );
        }

        if ($this->compareVersions('2.1.3')) {
            $this->addStuckOrderTable();
        }

        if ($this->compareVersions('2.1.4')) {
            $adapter->addIndex(
                $setup->getTable('digidirect_ai_queue_log'),
                $setup->getIdxName(
                    $setup->getTable('digidirect_ai_queue_log'),
                    ['log_id', 'queue_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['log_id', 'queue_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if ($this->compareVersions('2.1.5')) {
            $adapter->modifyColumn(
                $setup->getTable('digidirect_ai_queue'),
                'process_data',
                [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Process Data',
                ]
            );
        }

        if ($this->compareVersions('2.1.6')) {
            $adapter->addIndex(
                $setup->getTable('digidirect_ai_integrations'),
                $setup->getIdxName(
                    $setup->getTable('digidirect_ai_integrations'),
                    ['process_code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['process_code'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
        }

        if ($this->compareVersions('2.1.7')) {
            //DELETE DUPLICATES BEFORE UNIQUE INDEX ADDING
            $select = $adapter->select();
            $select->from(['s1' => $setup->getTable('digidirect_ai_schedule_run')], [])
                ->join(
                    ['s2' => $setup->getTable('digidirect_ai_schedule_run')],
                    implode(' AND ', [
                        's1.' . ScheduleInterface::PROCESS_CODE . ' = s2. ' . ScheduleInterface::PROCESS_CODE,
                        's1.' . ScheduleInterface::RUN_OPTIONS . ' = s2. ' . ScheduleInterface::RUN_OPTIONS,
                    ]),
                    []
                )
                ->where('s1.id < s2.id');
            $deleteSQL = $adapter->deleteFromSelect($select, 's1');
            $adapter->query($deleteSQL);

            $adapter->addIndex(
                $setup->getTable('digidirect_ai_schedule_run'),
                $setup->getIdxName(
                    $setup->getTable('digidirect_ai_schedule_run'),
                    [
                        ScheduleInterface::PROCESS_CODE,
                        ScheduleInterface::RUN_OPTIONS
                    ],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['process_code', 'run_options'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );

            $adapter->addColumn(
                $setup->getTable('digidirect_ai_schedule_run'),
                \Digidirect\AI\Api\Data\ScheduleInterface::IS_ADDED_BY_ADMIN,
                [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'Is added by admin',
                ]
            );
        }

        if ($this->compareVersions('2.1.8')) {
            $logsTable = $setup->getTable(LoggerResource::TABLE_NAME);

            if ($adapter->tableColumnExists($logsTable, 'date')) {
                $adapter->changeColumn(
                    $logsTable,
                    'date',
                    'created_at',
                    [
                        'type' => Table::TYPE_DATETIME,
                        'nullable' => true,
                        'comment' => 'Created At',
                    ]
                );
            }

            $newColumns = [
                'identifying_params' => [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Identifying Params',
                    'after' => 'comment',
                ],
                'updated_at' => [
                    'type' => Table::TYPE_DATETIME,
                    'nullable' => true,
                    'comment' => 'Updated At',
                    'after' => 'created_at',
                ]
            ];

            foreach ($newColumns as $columnName => $definition) {
                $method = $adapter->tableColumnExists($logsTable, $columnName) ? 'modifyColumn' : 'addColumn';
                $adapter->$method($logsTable, $columnName, $definition);
            }

            $adapter->update(
                $logsTable,
                ['updated_at' => new \Zend_Db_Expr('created_at')],
                'updated_at IS NULL'
            );

            //DELETE DUPLICATES BEFORE UNIQUE INDEX ADDING
            $logsDetailsTable = $setup->getTable(LoggerResource::TABLE_DETAILS_NAME);
            $select = $adapter->select();
            $select->from(['t1' => $logsDetailsTable], [])
                ->join(
                    ['t2' => $logsDetailsTable],
                    't1.log_id = t2.log_id',
                    []
                );

            if ($adapter->tableColumnExists($logsDetailsTable, 'id')) {
                $select->where('t1.id < t2.id');
            } else {
                $select->where('t1.details < t2.details');
            }

            $deleteSQL = $adapter->deleteFromSelect($select, 't1');
            $adapter->query($deleteSQL);

            if ($adapter->tableColumnExists($logsDetailsTable, 'id')) {
                $adapter->dropColumn($logsDetailsTable, 'id');
            }

            $indexList = $adapter->getIndexList($logsDetailsTable);
            $createPkIdx = true;
            foreach ($indexList as $idxInfo) {
                if ($idxInfo['type'] == AdapterInterface::INDEX_TYPE_PRIMARY) {
                    if ($idxInfo['fields'] == ['log_id']) {
                        $createPkIdx = false;
                    } else {
                        $adapter->dropIndex($logsDetailsTable, $idxInfo['KEY_NAME']);
                    }
                }
            }
            if ($createPkIdx) {
                $adapter->addIndex(
                    $logsDetailsTable,
                    $adapter->getIndexName($logsDetailsTable, ['log_id'], AdapterInterface::INDEX_TYPE_PRIMARY),
                    ['log_id'],
                    AdapterInterface::INDEX_TYPE_PRIMARY
                );
            }
        }

        if (version_compare($context->getVersion(), '2.1.9') < 0) {
            $setup->getConnection()->changeColumn(
                $setup->getTable('digidirect_ai_schedule_run'),
                'id',
                'id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'identity' => true,
                    'primary' => true,
                    'comment' => 'Record ID',
                ]
            );
        }

        if (version_compare($context->getVersion(), '2.1.11') < 0) {
            $queueLogTable = $setup->getTable('digidirect_ai_queue_log');
            if ($adapter->tableColumnExists($queueLogTable, 'entity_id')) {
                $adapter->dropColumn($queueLogTable, 'entity_id');
            }
            //set new primary key - log id. one log could not have more than 1 queue element.
            $pkIndexName = $adapter->getIndexName(
                $queueLogTable,
                ['log_id'],
                AdapterInterface::INDEX_TYPE_PRIMARY
            );
            $indexList = $adapter->getIndexList($queueLogTable);
            if (!isset($indexList[$pkIndexName])) {
                $adapter->addIndex($queueLogTable, $pkIndexName, ['log_id'], AdapterInterface::INDEX_TYPE_PRIMARY);
            }
            //remove old unique index
            $unqIndexName = $adapter->getIndexName(
                $queueLogTable,
                ['log_id', 'queue_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            );
            if (isset($indexList[$unqIndexName])) {
                $adapter->dropIndex($queueLogTable, $unqIndexName);
            }
        }

        $setup->endSetup();
    }

    /**
     * @param string $newVersion
     * @return bool
     */
    protected function compareVersions($newVersion)
    {
        return version_compare($this->context->getVersion(), $newVersion) < 0;
    }

    /**
     *
     * Add success finish date column
     * It will help to determine when integration was successfully finished
     * Need when we should use dates filter in request
     *
     * @return void
     */
    protected function addIntegrationRunDateTime()
    {
        $connection = $this->setup->getConnection();
        $tableName = $this->setup->getTable('digidirect_ai_integrations');
        if ($connection->isTableExists($tableName)) {
            if (!($connection->tableColumnExists($tableName, 'success_finish_date'))) {
                $connection->addColumn(
                    $tableName,
                    'success_finish_date',
                    [
                        'type' => Table::TYPE_TIMESTAMP,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Last time when integration finished with success status',
                    ]
                );
            }
        }
    }

    /**
     * Create Stuck Orders Information table
     *
     * @return void
     */
    protected function addStuckOrderTable()
    {
        $table = $this->setup->getTable(
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::AI_STUCK_ORDERS
        );

        $queueTable = $this->setup->getConnection()->newTable(
            $table
        )->addColumn(
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::ENTITY_ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Record ID'
        )->addColumn(
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::PROCESS_CODE,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Process Code'
        )->addColumn(
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::ORDER_ENTITY_ID,
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Order Entity ID'
        )->addColumn(
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::SKU,
            Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Product SKU'
        )->addColumn(
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::QTY,
            Table::TYPE_INTEGER,
            10,
            ['nullable' => false],
            'Product SKU'
        )->addColumn(
            'created_at',
            Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
            'Creation Time'
        )->addForeignKey(
            $this->setup->getFkName(
                $table,
                $this->setup->getTable('sales_order'),
                \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::ORDER_ENTITY_ID,
                OrderInterface::ENTITY_ID
            ),
            \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::ORDER_ENTITY_ID,
            $this->setup->getTable('sales_order'),
            OrderInterface::ENTITY_ID,
            Table::ACTION_CASCADE
        )->addIndex(
            $this->setup->getIdxName(
                $table,
                [
                    \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::PROCESS_CODE,
                    \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::ORDER_ENTITY_ID,
                    \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::SKU,
                ],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            [
                \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::PROCESS_CODE,
                \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::ORDER_ENTITY_ID,
                \Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\StuckOrderDb::SKU,
            ],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $this->setup->getConnection()->createTable($queueTable);
    }
}
