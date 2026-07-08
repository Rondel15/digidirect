<?php
namespace Digidirect\AbstractAttributes\Api;

/**
 * Option CRUD interface.
 * @api
 */
interface OptionRepositoryInterface
{
    /**
     * Create or update an option.
     * @param \Digidirect\AbstractAttributes\Api\Data\OptionInterface $option
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\CouldNotSaveException If problem during saving
     * @throws \Magento\Framework\Exception\NoSuchEntityException If product attr or aa with the ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Digidirect\AbstractAttributes\Api\Data\OptionInterface $option);

    /**
     * Get option by ID.
     * @param int $id
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the option ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($id);

    /**
     * Get option by ID.
     * @param int $optionId
     * @param int $storeId
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the option ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByOptionId($optionId, $storeId = null);

    /**
     * Get abstract attribute by option ID.
     * @param int $optionId
     * @param int $storeId
     * @return \Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the option ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAbstractAttribute($optionId, $storeId = null);

    /**
     * Get options by attribute ID.
     * @param int $attributeId
     * @param int $storeId
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException If aa with the option ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeOptions($attributeId, $storeId = null);

    /**
     * Delete option.
     * @param \Digidirect\AbstractAttributes\Api\Data\OptionInterface $option
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Digidirect\AbstractAttributes\Api\Data\OptionInterface $option);

    /**
     * Delete option by ID.
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);

    /**
     * Retrieve option which match a specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Digidirect\AbstractAttributes\Api\Data\OptionSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get collection
     * @return \Digidirect\AbstractAttributes\Model\ResourceModel\Option\Collection
     */
    public function getCollection();
}
