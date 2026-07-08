<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\CatalogRule;

use Digidirect\AI\Model\Lib\Entity\Import\ImportAbstract;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

class CatalogRule extends ImportAbstract implements CatalogRuleInterface
{
    const ENTITY_NAME = 'catalog_rule';

    /**
     * @var ResourceConnection
     */
    protected $connection;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var \Digidirect\AI\Helper\ArrayHelper
     */
    private $arrayHelper;

    /**
     * CatalogRule constructor.
     *
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param Json $json
     * @param \Digidirect\AI\Helper\ArrayHelper $arrayHelper
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        Json $json,
        \Digidirect\AI\Helper\ArrayHelper $arrayHelper,
        array $preparers = []
    ) {
        parent::__construct($beforeSaveValidator, $beforeUpdateValidator, $logger, $preparers);
        $this->connection = $resourceConnection->getConnection();
        $this->json = $json;
        $this->arrayHelper = $arrayHelper;
    }

    /**
     * Get name of entity
     *
     * @return string
     */
    public function getName()
    {
        return static::ENTITY_NAME;
    }

    /**
     * @param array $entity
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _save(array $entity, $updateOnDuplicate = true)
    {
        return $this->_saveBunch([$entity]);
    }

    /**
     * @param array $entities
     * @param bool $updateOnDuplicate
     * @return bool
     */
    protected function _saveBunch(array $entities, $updateOnDuplicate = true)
    {
        list($entitiesForInsert, $entitiesForUpdate) = $this->separateEntitiesForInsertAndUpdate($entities);

        $this->updateEntities($entitiesForUpdate);
        $insertedEntities = $this->insertEntities($entitiesForInsert);

        $processedEntities = array_merge($entitiesForUpdate, $insertedEntities);
        $this->updateRelations($processedEntities);
    }

    /**
     * @param array $entity
     * @return bool
     */
    protected function _update(array $entity)
    {
        return $this->_saveBunch([$entity]);
    }

    /**
     * @param array $entities
     * @return bool
     */
    protected function _updateBunch(array $entities)
    {
        return $this->_saveBunch($entities);
    }

    /**
     * @param array $entities
     * @return array
     */
    protected function getEntitiesData($entities)
    {
        $incrementId = (int)$this->connection->showTableStatus('sequence_catalogrule')['Auto_increment'];
        $rowIncrementId = (int)$this->connection->showTableStatus('catalogrule')['Auto_increment'];
        foreach ($entities as $key => &$entity) {
            foreach ($entity as $field => $value) {
                if (in_array($field, ['conditions_serialized', 'actions_serialized']) and is_array($entity[$field])) {
                    $entity[$field] = $this->json->serialize($entity[$field]);
                    continue;
                }
                if (is_array($value)) {
                    unset($entity[$field]);
                }
            }
            $entity['rule_id'] = $entity['rule_id'] ?? $incrementId++;
            $entity['row_id'] = $entity['row_id'] ?? $rowIncrementId++;
        }

        return $entities;
    }

    /**
     * @param array $entity
     * @param string $column
     * @return int
     */
    protected function insertIntoLinkedTable($entity, $column)
    {
        if (empty($entity)) {
            return;
        }
        $dataForInsert = [];
        foreach ($entity[$column . 's'] as $linkedId) {
            $dataForInsert[] = [
                'row_id' => $entity['row_id'],
                $column => $linkedId
            ];
        }
        return $this->connection->insertOnDuplicate('catalogrule' . '_' . rtrim($column, '_id'), $dataForInsert);
    }

    /**
     * @param array $entities
     * @return array
     */
    protected function separateEntitiesForInsertAndUpdate(array $entities)
    {
        $entitiesForInsert = [];
        $entitiesForUpdate = [];
        foreach ($entities as $entity) {
            if (isset($entity['row_id']) and isset($entity['rule_id'])) {
                $entitiesForUpdate[] = $entity;
            } else {
                $entitiesForInsert[] = $entity;
            }
        }
        return [$entitiesForInsert, $entitiesForUpdate];
    }

    /**
     * @param array $entitiesForUpdate
     * @return $this
     */
    protected function updateEntities($entitiesForUpdate)
    {
        if (!empty($entitiesForUpdate)) {
            $entitiesData = $this->getEntitiesData($entitiesForUpdate);
            $this->connection->insertOnDuplicate('catalogrule', $entitiesData);
        }
        return $this;
    }

    /**
     * @param array $entitiesForInsert
     * @return array
     */
    protected function insertEntities($entitiesForInsert)
    {
        $insertedEntities = [];
        if (!empty($entitiesForInsert)) {
            $entitiesData = $this->getEntitiesData($entitiesForInsert);

            $ruleIds = array_column($entitiesData, 'rule_id');
            $this->connection->insertArray('sequence_catalogrule', ['sequence_value'], array_unique($ruleIds));
            $this->connection->insertMultiple('catalogrule', $entitiesData);

            $insertedEntities = $entitiesForInsert;
            $this->arrayHelper
                ->addColumn($insertedEntities, 'rule_id', $ruleIds)
                ->addColumn($insertedEntities, 'row_id', array_column($entitiesData, 'row_id'));
        }
        return $insertedEntities;
    }

    /**
     * @param array $processedEntities
     * @return $this
     */
    protected function updateRelations($processedEntities)
    {
        foreach ($processedEntities as $processedEntity) {
            $this->insertIntoLinkedTable(
                $processedEntity ?? [],
                'customer_group_id'
            );
            $this->insertIntoLinkedTable(
                $processedEntity ?? [],
                'website_id'
            );
        }
        return $this;
    }
}
