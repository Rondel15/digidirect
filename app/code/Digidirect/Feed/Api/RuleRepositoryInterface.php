<?php

namespace Digidirect\Feed\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface RuleRepositoryInterface
{
    /**
     * @param Data\RuleInterface $rule
     * @return Data\RuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return Data\RuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($ruleId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\RuleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\RuleInterface $rule
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\RuleInterface $rule);

    /**
     * @param int $ruleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($ruleId);
}
