<?php

namespace Digidirect\AbstractEntity\Model\Rest;

use Digidirect\AbstractEntity\Api\AbstractEntityRepositoryInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterfaceFactory;
use Digidirect\AbstractEntity\Api\Rest\AbstractEntityRestByAttributeSetInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class AbstractEntityRestByAttributeSet implements AbstractEntityRestByAttributeSetInterface
{
    /**
     * @var AbstractEntityRepositoryInterface
     */
    protected $aeRepository;

    /**
     * @var AbstractEntityInterfaceFactory
     */
    protected $aeFactory;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var null|array
     */
    protected $attributeSetNameToId;

    /**
     * AbstractEntityRestByAttributeSet constructor.
     * @param AbstractEntityRepositoryInterface $aeRepository
     * @param AbstractEntityInterfaceFactory $aeFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param EavConfig $eavConfig
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        AbstractEntityRepositoryInterface $aeRepository,
        AbstractEntityInterfaceFactory $aeFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        EavConfig $eavConfig,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->aeRepository = $aeRepository;
        $this->aeFactory = $aeFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->eavConfig = $eavConfig;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function transformAttributeSetName($name)
    {
        $name = str_replace(' ', '_', $name);
        $name = strtolower($name);
        return $name;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAttributeSetInfo()
    {
        if ($this->attributeSetNameToId === null) {
            $result = [];
            $entityTypeId = $this->eavConfig->getEntityType(AbstractEntity::ENTITY_TYPE)->getEntityTypeId();
            $criteria = $this->searchCriteriaBuilder
                ->addFilter(Set::KEY_ENTITY_TYPE_ID, $entityTypeId)
                ->create();
            $searchResult = $this->attributeSetRepository->getList($criteria);
            foreach ($searchResult->getItems() as $attributeSet) {
                $name = $attributeSet->getAttributeSetName();
                $transformedAttributeSetName = $this->transformAttributeSetName($name);
                $result[$transformedAttributeSetName] = [
                    'id' => (int)$attributeSet->getId(),
                    'name' => $name,
                ];
            }
            $this->attributeSetNameToId = $result;
        }
        return $this->attributeSetNameToId;
    }

    /**
     * @param string $attributeSetName
     * @param string $field
     * @return mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function getAttributeSetField($attributeSetName, $field)
    {
        $attributeSetToName = $this->getAttributeSetInfo();
        $transformedAttributeSetName = $this->transformAttributeSetName($attributeSetName);
        if (!isset($attributeSetToName[$transformedAttributeSetName])) {
            throw new NoSuchEntityException(
                __('Abstract Entity "%1" does not exist.', $attributeSetName)
            );
        }
        return $attributeSetToName[$transformedAttributeSetName][$field];
    }

    /**
     * @param string $attributeSetName
     * @param SearchCriteriaInterface|null $searchCriteria
     * @param string $attributes
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntitySearchResultsInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getList($attributeSetName, SearchCriteriaInterface $searchCriteria = null, $attributes = '*')
    {
        $exactAttributeSetName = $this->getAttributeSetField($attributeSetName, 'name');
        if ($searchCriteria === null) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        }
        if ($attributes !== '*') {
            $attributes = $attributes ? explode(',', $attributes) : null;
        }
        return $this->aeRepository->getList($searchCriteria, $exactAttributeSetName, $attributes);
    }

    /**
     * @param string $attributeSetName
     * @param int $id
     * @return AbstractEntityInterface
     * @throws LocalizedException
     */
    public function getById($attributeSetName, $id)
    {
        $entity = $this->aeRepository->getById($id);
        $attributeSetId = $this->getAttributeSetField($attributeSetName, 'id');
        if ($entity->getAttributeSetId() != $attributeSetId) {
            throw new NoSuchEntityException(
                __('Abstract Entity "%1" with id "%2" does not exist.', $attributeSetName, $id)
            );
        }

        return $entity;
    }

    /**
     * @param string $attributeSetName
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($attributeSetName, $id)
    {
        $entity = $this->aeRepository->getById($id);
        $attributeSetId = $this->getAttributeSetField($attributeSetName, 'id');
        if ($entity->getAttributeSetId() != $attributeSetId) {
            throw new NoSuchEntityException(
                __('Abstract Entity "%1" with id "%2" does not exist.', $attributeSetName, $id)
            );
        }
        $this->aeRepository->delete($entity);
        return true;
    }

    /**
     * @param AbstractEntityInterface|AbstractEntity $entity
     * @param string $attributeSetName
     * @return AbstractEntityInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function create(AbstractEntityInterface $entity, $attributeSetName)
    {
        $attributeSetId = $this->getAttributeSetField($attributeSetName, 'id');
        if ($entity->getId()) {
            throw new CouldNotSaveException(__('Invalid data. Could not save entity with specified ID.'));
        }

        if ($entity->hasData(AbstractEntityInterface::ATTRIBUTE_SET_ID) &&
            $entity->getAttributeSetId() != $attributeSetId
        ) {
            throw new CouldNotSaveException(__('Specified attribute set id doesn\'t match to attribute set name.'));
        }

        $entity->setAttributeSetId($attributeSetId);
        $entityToSave = $this->aeFactory->create();
        $entityToSave->setStoreId(0);

        return $this->saveFromSource($entityToSave, $entity);
    }

    /**
     * @param AbstractEntityInterface|AbstractEntity $entity
     * @param string $attributeSetName
     * @param int $id
     * @return AbstractEntityInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function update(AbstractEntityInterface $entity, $attributeSetName, $id)
    {
        /**
         * @var $existingEntity AbstractEntity
         */
        $attributeSetId = $this->getAttributeSetField($attributeSetName, 'id');
        if ($entity->getId() && $entity->getId() != $id) {
            throw new CouldNotSaveException(__('Invalid operation. ID from url doesn\'t match to ID from body.'));
        }

        if ($entity->hasData(AbstractEntityInterface::ATTRIBUTE_SET_ID) &&
            $entity->getAttributeSetId() != $attributeSetId
        ) {
            throw new CouldNotSaveException(__('Specified attribute set id doesn\'t match to attribute set name.'));
        }

        $existingEntity = $this->aeRepository->getById($id);
        if ($existingEntity->getAttributeSetId() != $attributeSetId) {
            throw new NoSuchEntityException(
                __('Abstract Entity "%1" with id "%2" does not exist.', $attributeSetName, $id)
            );
        }

        $entity->setId($id);
        $entity->setAttributeSetId($attributeSetId);
        if (!$entity->hasData(AbstractEntityInterface::STATUS)
            && $existingEntity->hasData(AbstractEntityInterface::STATUS)
        ) {
            $entity->setStatus($existingEntity->getStatus());
        }

        $extensionAttributes = $entity->getExtensionAttributes();
        if ($extensionAttributes === null || empty($extensionAttributes->__toArray())) {
            $entity->setExtensionAttributes($existingEntity->getExtensionAttributes());
        }

        return $this->saveFromSource($existingEntity, $entity);
    }

    /**
     * @param AbstractEntityInterface $entity
     * @param AbstractEntityInterface $sourceEntity
     * @return AbstractEntityInterface|AbstractEntity
     * @throws LocalizedException
     */
    protected function saveFromSource(AbstractEntityInterface $entity, AbstractEntityInterface $sourceEntity)
    {
        $entityDataArray = $this->getEntityDataArrayToSave($sourceEntity);
        $entity = $this->initializeAbstractEntityData($entity, $entityDataArray);
        $entity = $this->aeRepository->save($entity);
        $entity = $this->aeRepository->getById($entity->getId());
        return $entity;
    }

    /**
     * @param AbstractEntityInterface $entity
     * @return array
     */
    protected function getEntityDataArrayToSave(AbstractEntityInterface $entity)
    {
        $entityDataArray = $this->extensibleDataObjectConverter->toNestedArray(
            $entity,
            [],
            AbstractEntityInterface::class
        );

        $entityDataArray = array_replace($entityDataArray, $entity->getData());
        return $entityDataArray;
    }

    /**
     * Merge data from DB and updates from request
     *
     * @param AbstractEntityInterface|AbstractEntity $entity
     * @param array $entityData
     * @return AbstractEntityInterface|AbstractEntity
     */
    protected function initializeAbstractEntityData(AbstractEntityInterface $entity, array $entityData)
    {
        foreach ($entityData as $key => $value) {
            $entity->setData($key, $value);
        }

        return $entity;
    }
}
