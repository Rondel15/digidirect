<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\GiftCardAccount;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class History
 *
 * @package Digidirect\AI\Model\Lib\Entity\Import\GiftCardAccount
 */
class History extends DataObject
{
    const MAIN_TABLE = 'magento_giftcardaccount_history';
    const HISTORY = 'history';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var array
     */
    protected $fields;

    /**
     * History constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param array $fields
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        array $fields = []
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->fields = array_keys($fields);
    }

    /**
     * @param array $entity
     * @return int
     */
    public function save($entity)
    {
        $history = $entity[self::HISTORY] ?? false;
        if (!$history) {
            return 0;
        }

        $id = $entity[GiftCardAccount::GIFTCARDACCOUNT_ID];
        foreach ($history as &$value) {
            $value[GiftCardAccount::GIFTCARDACCOUNT_ID] = $id;
            $value = $this->getEntityData($value);
        }

        return $this->connection->insertOnDuplicate($this->connection->getTableName(self::MAIN_TABLE), $history);
    }

    /**
     * @param array $entity
     * @return $this
     */
    protected function getEntityData($entity)
    {
        $newEntity = [];
        foreach ($this->fields as $field) {
            $newEntity[$field] = $entity[$field] ?? '(DEFAULT)';
        }

        return $newEntity;
    }
}
