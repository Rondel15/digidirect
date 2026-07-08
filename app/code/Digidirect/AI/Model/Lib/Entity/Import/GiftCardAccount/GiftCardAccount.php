<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\GiftCardAccount;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EventManager;

/**
 * Class GiftCardAccount
 *
 * @package Digidirect\AI\Model\Lib\Entity\Import\GiftCardAccount
 */
class GiftCardAccount extends ImportAbstract implements GiftCardAccountInterface
{
    const ENTITY_NAME = 'giftcardaccount';
    const MAIN_TABLE = 'magento_giftcardaccount';
    const GIFTCARDACCOUNT_ID = 'giftcardaccount_id';
    const CODE = 'code';

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
     * @var array
     */
    protected $fields;

    /**
     * ImportAbstract constructor.
     *
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param EventManager $eventManager
     * @param ResourceConnection $resourceConnection
     * @param History $history
     * @param array $fields
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        EventManager $eventManager,
        ResourceConnection $resourceConnection,
        History $history,
        array $fields = [],
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
        $this->fields = array_keys($fields);
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
        $this->connection->beginTransaction();
        $this->eventDispatch('save_before', ['entities' => $entities, 'updateOnDuplicate' => $updateOnDuplicate]);

        list($forUpdate, $forInsert) = $this->separateInsertAndUpdate($entities);
        $result = $this->updateEntities($forUpdate) || $this->insertEntities($forInsert);
        $this->connection->commit();
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
     * @return array
     */
    protected function getEntitiesData($entities)
    {
        $entitiesData = [];
        foreach ($entities as $entity) {
            $newEntity = [];
            foreach ($this->fields as $field) {
                if (array_key_exists($field, $entity)) {
                    $newEntity[$field] = $entity[$field];
                }
            }
            $newEntity['date_created'] = $newEntity['date_created'] ?? date("Y-m-d");
            $entitiesData[] = $newEntity;
        }

        return $entitiesData;
    }

    /**
     * @param array $entities
     * @return array
     */
    protected function separateInsertAndUpdate(array $entities)
    {
        $entitiesForUpdate = [];
        $entitiesForInsert = [];
        $ids = array_column($entities, self::GIFTCARDACCOUNT_ID);
        $codes = array_column($entities, self::CODE);
        if (empty($ids) && empty($codes)) {
            return [[], $entities];
        }
        $select = $this->connection
            ->select()
            ->from(self::MAIN_TABLE, [self::GIFTCARDACCOUNT_ID, self::CODE])
            ->where(self::CODE . ' in (?)', $codes);
        if ($ids) {
            $select->orWhere(self::GIFTCARDACCOUNT_ID . ' in (?)', $ids);
        }
        $existingEntities = $this->connection->fetchAll($select);

        if (!empty($existingEntities)) {
            foreach ($existingEntities as $existingEntity) {
                foreach ($entities as $key => $entity) {
                    $idExists = isset($entity[self::GIFTCARDACCOUNT_ID])
                    and $entity[self::GIFTCARDACCOUNT_ID] == $existingEntity[self::GIFTCARDACCOUNT_ID];

                    if ($idExists || $entity[self::CODE] == $existingEntity[self::CODE]) {
                        $entity[self::GIFTCARDACCOUNT_ID] = $existingEntity[self::GIFTCARDACCOUNT_ID];
                        $entitiesForUpdate[] = $entity;
                        unset($entities[$key]);
                        break;
                    }
                }
            }
            $entitiesForInsert = $entities;
        }

        return [$entitiesForUpdate, $entitiesForInsert ?: $entities];
    }

    /**
     * @param array $forUpdate
     * @return int
     */
    protected function updateEntities($forUpdate)
    {
        $entitiesData = $this->getEntitiesData($forUpdate);
        $result = 0;
        if (!empty($entitiesData)) {
            foreach ($entitiesData as $entity) {
                $result += $this->connection->update(
                    $this->connection->getTableName(self::MAIN_TABLE),
                    $entity,
                    [ (self::GIFTCARDACCOUNT_ID . ' = ?') => $entity[self::GIFTCARDACCOUNT_ID]]
                );
            }
            foreach ($forUpdate as $entity) {
                $this->history->save($entity);
            }
        }

        return $result;
    }

    /**
     * @param array $forInsert
     * @return int
     */
    protected function insertEntities($forInsert)
    {
        $entitiesData = $this->getEntitiesData($forInsert);
        $result = 0;
        if (!empty($entitiesData)) {
            foreach ($entitiesData as $entity) {
                $result += $this->connection->insert(
                    $this->connection->getTableName(self::MAIN_TABLE),
                    $entity
                );
                $lastInsertId = $this->connection->lastInsertId($this->connection->getTableName(self::MAIN_TABLE));
                $entity[self::GIFTCARDACCOUNT_ID] = $lastInsertId;
                $this->history->save($entity);
            }
        }

        return $result;
    }
}
