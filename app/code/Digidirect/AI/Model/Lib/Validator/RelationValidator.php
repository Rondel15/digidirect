<?php
namespace Digidirect\AI\Model\Lib\Validator;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

class RelationValidator implements \Zend_Validate_Interface
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var array
     */
    protected $exists;

    /**
     * @var array
     */
    private $unique;

    /**
     * @var null
     */
    private $mainTable;

    /**
     * RelationValidator constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param null $mainTable
     * @param array $exists
     * @param array $unique
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        $mainTable = null,
        $exists = [],
        $unique = []
    ) {

        $this->connection = $resourceConnection->getConnection();
        $this->exists = $exists;
        $this->unique = $unique;
        $this->mainTable = $mainTable;
    }

    /**
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        return $this->checkExists($value) && $this->checkUnique($value);
    }

    /**
     * @param array $value
     * @return bool
     */
    protected function checkExists($value)
    {
        foreach ($this->exists as $field => $relation) {
            if (!isset($value[$field])) {
                continue;
            }
            list($tableName, $columnName) = explode('.', $relation);
            $select = $this->connection->select()
                ->from($this->connection->getTableName($tableName), '(1)')
                ->where($columnName . ' = ?', $value[$field])
                ->limit(1);
            $isExists = $this->connection->fetchOne($select);
            if (!$isExists) {
                $this->addMessage("Relation from '$field' to '$relation' does not exists");

                return false;
            }
        }

        return true;
    }

    /**
     * @param array $value
     * @return bool
     */
    protected function checkUnique($value)
    {
        foreach ($this->unique as $fields) {
            $fieldsArray = explode(',', $fields);

            $select = $this->connection->select()
                ->from($this->connection->getTableName($this->mainTable), '(1)')
                ->limit(1);
            foreach ($fieldsArray as $fieldName) {
                $select->where($fieldName . ' = ?', $value[$fieldName]);
            }

            $isExists = $this->connection->fetchOne($select);
            if ($isExists) {
                $this->addMessage("Fields '$fields' must be unique");

                return false;
            }
        }

        return true;
    }

    /**
     * @param string $message
     * @return $this
     */
    protected function addMessage($message)
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
