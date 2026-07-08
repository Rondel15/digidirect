<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\RewardPoint;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Lib\Entity\Import\Service\Source\ArraySourceFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EventManager;

/**
 * Class RewardPoint
 *
 * @package Digidirect\AI\Model\Lib\Entity\Import\RewardPoint
 */
class RewardPoint extends ImportAbstract implements RewardPointInterface
{
    const ENTITY_NAME = 'reward_point';
    const MAIN_TABLE = 'magento_reward';

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var string
     */
    protected $eventPrefix = 'digidirect_ai_import_';

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var History
     */
    protected $history;

    /**
     * ImportAbstract constructor.
     *
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param EventManager $eventManager
     * @param ResourceConnection $resourceConnection
     * @param History $history
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        EventManager $eventManager,
        ResourceConnection $resourceConnection,
        History $history,
        array $preparers = []
    ) {
        parent::__construct(
            $beforeSaveValidator,
            $beforeUpdateValidator,
            $logger,
            $preparers
        );
        $this->eventManager = $eventManager;
        $this->connection = $resourceConnection->getConnection();
        $this->history = $history;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::ENTITY_NAME;
    }

    /**
     * @param array $entity
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _save(array $entity, $updateOnDuplicate = true)
    {
        return $this->_saveBunch([$entity], $updateOnDuplicate);
    }

    /**
     * @param array $entities
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _saveBunch(array $entities, $updateOnDuplicate = true)
    {
        $entitiesData = $this->getEntitiesData($entities);
        $this->eventDispatch('save_before', ['entities' => $entities, 'updateOnDuplicate' => $updateOnDuplicate]);

        if ($updateOnDuplicate) {
            $result = $this->connection->insertOnDuplicate(
                $this->connection->getTableName(self::MAIN_TABLE),
                $entitiesData
            );
        } else {
            $result = $this->connection->insertMultiple(
                $this->connection->getTableName(self::MAIN_TABLE),
                $entitiesData
            );
        }
        $this->saveRelations($entities);
        $this->eventDispatch('save_after', [
            'entities' => $entities,
            'updateOnDuplicate' => $updateOnDuplicate,
            'result' => $result,
        ]);
        $this->eventDispatch('after');

        return $result;
    }

    /**
     * @param array $entity
     * @return bool
     */
    protected function _update(array $entity)
    {
        return $this->_updateBunch([$entity]);
    }

    /**
     * @param array $entities
     * @return bool
     */
    protected function _updateBunch(array $entities)
    {
        $this->eventDispatch('update_before', ['entities' => $entities]);
        $result = $this->connection->insertOnDuplicate($this->connection->getTableName(self::MAIN_TABLE), $entities);
        $this->saveRelations($entities);
        $this->eventDispatch('update_after', ['entities' => $entities, 'result' => $result]);
        $this->eventDispatch('after');

        return $result;
    }

    /**
     * @param string $key
     * @param array $data
     * @return void
     */
    protected function eventDispatch($key, array $data = [])
    {
        $this->eventManager->dispatch($this->eventPrefix . $key, $data);
    }

    /**
     * @param array $entities
     * @return $this
     */
    protected function saveRelations(array $entities)
    {
        foreach ($entities as $entity) {
            $this->history->save($entity);
        }

        return $this;
    }

    /**
     * @param array $entities
     * @return array
     */
    protected function getEntitiesData($entities)
    {
        $entitiesData = [];
        foreach ($entities as $entity) {
            foreach ($entity as $key => $value) {
                if (is_array($value)) {
                    unset($entity[$key]);
                }
            }
            $entitiesData[] = $entity;
        }
        return $entitiesData;
    }
}
