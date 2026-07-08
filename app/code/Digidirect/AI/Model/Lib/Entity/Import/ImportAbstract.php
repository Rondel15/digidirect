<?php
namespace Digidirect\AI\Model\Lib\Entity\Import;

use Digidirect\AI\Model\Logger\LoggerInterface;
use Digidirect\AI\Model\Lib\Validator\Validate as Validator;
use Digidirect\AI\Model\Lib\Entity\Import\Service\PreparerInterface;
use Magento\Framework\Exception\CouldNotSaveException;

abstract class ImportAbstract implements ImportInterface
{
    /**
     * @var Validator
     */
    protected $_beforeSaveValidator;

    /**
     * @var Validator
     */
    protected $_beforeUpdateValidator;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var PreparerInterface[]
     */
    protected $_preparers;

    /**
     * ImportAbstract constructor.
     * @param Validator $beforeSaveValidator
     * @param Validator $beforeUpdateValidator
     * @param LoggerInterface $logger
     * @param array $preparers
     */
    public function __construct(
        Validator $beforeSaveValidator,
        Validator $beforeUpdateValidator,
        LoggerInterface $logger,
        array $preparers = []
    ) {
        $this->_beforeSaveValidator = $beforeSaveValidator;
        $this->_beforeUpdateValidator = $beforeUpdateValidator;
        $this->_logger = $logger;
        $this->_preparers = $preparers;
    }

    /**
     * @param array $entity
     * @param bool $updateOnDuplicate
     * @return bool
     */
    abstract protected function _save(array $entity, $updateOnDuplicate = true);

    /**
     * @param array $entities
     * @param bool $updateOnDuplicate
     * @return bool
     */
    abstract protected function _saveBunch(array $entities, $updateOnDuplicate = true);

    /**
     * @param array $entity
     * @return bool
     */
    abstract protected function _update(array $entity);

    /**
     * @param array $entities
     * @return bool
     */
    abstract protected function _updateBunch(array $entities);

    /**
     * @param array $entity
     * @param bool $updateOnDuplicate
     * @return bool
     */
    public function save(array $entity, $updateOnDuplicate = true)
    {
        $this->validateBeforeSave($entity);
        $this->prepareEntity($entity);
        return $this->_save($entity, $updateOnDuplicate);
    }

    /**
     * @param array $entities
     * @param bool $updateOnDuplicate
     * @return bool
     */
    public function saveBunch(array $entities, $updateOnDuplicate = true)
    {
        $entities = $this->getValidBunch($this->_beforeSaveValidator, $entities);
        if (!empty($entities)) {
            $this->prepareEntities($entities);
            return $this->_saveBunch($entities, $updateOnDuplicate);
        }
        return false;
    }

    /**
     * @param array $entity
     * @return bool
     */
    public function update(array $entity)
    {
        $this->validateBeforeUpdate($entity);
        $this->prepareEntity($entity);
        return $this->_update($entity);
    }

    /**
     * @param array $entities
     * @return bool
     */
    public function updateBunch(array $entities)
    {
        $entities = $this->getValidBunch($this->_beforeUpdateValidator, $entities);
        if (!empty($entities)) {
            $this->prepareEntities($entities);
            return $this->_updateBunch($entities);
        }
        return false;
    }

    /**
     * Validate entity before saving it
     *
     * @param array $entity
     * @return bool
     */
    public function validateBeforeSave(array $entity)
    {
        return $this->_validateEntity($this->_beforeSaveValidator, $entity);
    }

    /**
     * Validate entity before updating it
     *
     * @param array $entity
     * @return bool
     */
    public function validateBeforeUpdate(array &$entity)
    {
        return $this->_validateEntity($this->_beforeUpdateValidator, $entity);
    }

    /**
     * Prepare entity before process
     *
     * @param array $entity
     * @return void
     */
    public function prepareEntity(array &$entity)
    {
        foreach ($this->_preparers as $preparer) {
            if ($preparer instanceof PreparerInterface) {
                $preparer->prepareEntity($entity);
            }
        }
    }

    /**
     * Prepare entities before process
     *
     * @param array $entities
     * @return void
     */
    public function prepareEntities(array &$entities)
    {
        foreach ($entities as &$entity) {
            $this->prepareEntity($entity);
        }
    }

    /**
     * Validate entity
     *
     * @param Validator $validator
     * @param array $entity
     * @return bool
     * @throws CouldNotSaveException
     */
    protected function _validateEntity(Validator $validator, array $entity)
    {
        if ($validator->isValid($entity) === false) {
            $message = __('Import Data is not valid.');
            $this->_logger->critical(
                $message,
                [
                    'entity' => $this->getName(),
                    'errors' => $validator->getMessages()
                ]
            );
            throw new CouldNotSaveException($message);
        }
        return true;
    }

    /**
     * Validate entities bunch
     *
     * @param Validator $validator
     * @param array $entities
     * @return bool
     * @throws CouldNotSaveException
     */
    protected function _validateBunch(Validator $validator, array $entities)
    {
        return !empty($this->getValidBunch($validator, $entities));
    }

    /**
     * @param Validator $validator
     * @param array $entities
     * @return array
     * @throws CouldNotSaveException
     */
    protected function getValidBunch(Validator $validator, array $entities)
    {
        $data = [];
        foreach ($entities as $entityNumber => $entity) {
            try {
                $this->_validateEntity($validator, $entity);
                $data[] = $entity;
            } catch (CouldNotSaveException $e) {
                $this->_logger->warning(__('Entity #%1 is not valid. Skipped.', $entityNumber), [
                    'entityName' => $this->getName(),
                    'entityData' => $entity,
                    'errors' => $e->getMessage()
                ]);
            }
        }

        if (empty($data)) {
            $this->_logger->notice(__('Entities bunch data is empty.'));
            return $data;
        }

        return $data;
    }
}
