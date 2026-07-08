<?php
namespace Digidirect\AbstractAttributes\Api;

/**
 * AbstractAttributes CRUD interface.
 * @api
 */
interface AbstractAttributeRepositoryInterface
{
    /**
     * Create or update an abstract attribute.
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute);

    /**
     * Get abstract attribute by ID.
     * @param int $id
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the attribute ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Get abstract attribute by attribute ID.
     * @param int $attributeId
     * @param int $storeId
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the attribute ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByAttributeId($attributeId, $storeId = null);

    /**
     * Get abstract attribute list.
     * @param bool|null $status If null - don't check status, if bool - use for status sort
     * @param int $storeId
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface[]
     */
    public function getAbstractAttributes($status = null, $storeId = null);

    /**
     * Retrieve abstract attributes which match a specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete abstract attribute.
     * @param \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface $abstractAttribute);

    /**
     * Delete abstract attribute by attribute ID.
     * @param int $abstractAttributeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($abstractAttributeId);
}
