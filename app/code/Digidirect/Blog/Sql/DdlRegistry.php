<?php

namespace Digidirect\Blog\Sql;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * As we use describe table expression to get columns we make registry not to call cache every time
 *
 * @since 2.0.0
 */
class DdlRegistry
{
    /**
     * @var array
     */
    private $ddlRegistry = [];

    /**
     * @param string $table
     * @param AdapterInterface $adapter
     * @return []
     */
    public function getTableColumns(string $table, AdapterInterface $adapter)
    {
        if (!isset($this->ddlRegistry[$table])) {
            try {
                $this->ddlRegistry[$table] = array_keys($adapter->describeTable($table));
            } catch (\Throwable $exception) {
                $this->ddlRegistry[$table] = [];
            }
        }

        return $this->ddlRegistry[$table];
    }
}
