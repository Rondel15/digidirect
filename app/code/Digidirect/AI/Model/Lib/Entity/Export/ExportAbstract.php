<?php
namespace Digidirect\AI\Model\Lib\Entity\Export;

use Magento\Framework\DB\Select;

abstract class ExportAbstract implements ExportInterface
{
    /**
     * @var Select
     */
    protected $select;

    /**
     * @var Select
     */
    protected $connection;

    /**
     * @var string[]
     */
    protected $aliases;

    /**
     * ExportAbstract constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param array $aliases
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        $aliases = []
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->select = $this->connection->select();
        $this->aliases = array_flip($aliases);
    }

    /**
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @return string[]
     */
    public function fetchAll()
    {
        return $this->connection->fetchAll($this->select);
    }

    /**
     * @param string $type
     * @param string $table
     * @param string $cond
     * @return $this
     */
    public function joinOnce(
        $type = 'join',
        $table = '',
        $cond = ''
    ) {
        $cond = $this->replaceTablesByAlias($cond);
        $tableName = $table;
        if (is_string($table)) {
            $alias = $this->aliases[$tableName] ?? $table;
            $table = [$alias => $this->connection->getTableName($tableName)];
        }
        if (is_array($table)) {
            $tableName = current($table);
            $table = [key($table) => $this->connection->getTableName($tableName)];
        }
        $isJoined = isset($this->select->getPart('from')[$tableName]);
        if (!$isJoined) {
            $this->select->{$type}($table, $cond, []);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function distinct()
    {
        $this->select->distinct();

        return $this;
    }

    /**
     * @param string $cond
     * @param string $value
     * @return $this
     */
    public function where($cond, $value)
    {
        $this->select->where($cond, $value);

        return $this;
    }

    /**
     * @param string $sql
     * @return mixed
     */
    protected function replaceTablesByAlias($sql)
    {
        foreach ($this->aliases as $table => $alias) {
            $sql = str_replace('{' . $table . '}', $alias, $sql);
        }
        $sql = str_replace(['{', '}'], '', $sql);

        return $sql;
    }
}
