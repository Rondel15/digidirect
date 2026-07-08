<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\RewardPoint;

use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySourceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class History
 *
 * @package Digidirect\AI\Model\Lib\Entity\Import\RewardPoint
 */
class History
{
    const MAIN_TABLE = 'magento_reward_history';
    const HISTORY = 'history';
    const REWARD_ID = 'reward_id';
    const CUSTOMER_ID = 'customer_id';
    const WEBSITE_ID = 'website_id';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * RewardPointHistory constructor.
     *
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->connection = $resourceConnection->getConnection();
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

        $id = $this->getRewardId($entity);
        foreach ($history as &$value) {
            $value[self::REWARD_ID] = $id;
        }

        return $this->connection->insertOnDuplicate($this->connection->getTableName(self::MAIN_TABLE), $history);
    }

    /**
     * @param array $entity
     * @return string
     */
    protected function getRewardId($entity)
    {
        if (isset($entity[self::REWARD_ID])) {
            $id = $entity[self::REWARD_ID];
        } else {
            $select = $this->connection->select()
                ->from($this->connection->getTableName(RewardPoint::MAIN_TABLE), self::REWARD_ID)
                ->where(self::CUSTOMER_ID . ' = ?', $entity[self::CUSTOMER_ID])
                ->where(self::WEBSITE_ID . ' = ?', $entity[self::WEBSITE_ID])
                ->limit(1);
            $id = $this->connection->fetchOne($select);
        };

        return $id;
    }
}
