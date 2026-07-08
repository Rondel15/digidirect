<?php

namespace Digidirect\AbstractEntity\Api\Rest;

/**
 * Interface AbstractEntityAttributeSetRestInterface
 * @package Digidirect\AbstractEntity\Api\Rest
 */
interface AbstractEntityRestByAttributeSetInterface
{
    /**
     * @param string $attributeSetName
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @param string $attributes
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntitySearchResultsInterface
     */
    public function getList(
        $attributeSetName,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null,
        $attributes = '*'
    );

    /**
     * @param string $attributeSetName
     * @param int $id
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($attributeSetName, $id);

    /**
     * @param string $attributeSetName
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($attributeSetName, $id);

    /**
     * @param \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface $entity
     * @param string $attributeSetName
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function create(\Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface $entity, $attributeSetName);

    /**
     * @param \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface $entity
     * @param string $attributeSetName
     * @param int $id
     * @return \Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function update(\Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface $entity, $attributeSetName, $id);
}
