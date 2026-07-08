<?php

namespace Digidirect\Feed\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TemplateRepositoryInterface
{
    /**
     * @param Data\TemplateInterface $template
     * @return Data\TemplateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\TemplateInterface $template);

    /**
     * @param int $templateId
     * @return Data\TemplateInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($templateId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\TemplateSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\TemplateInterface $template
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\TemplateInterface $template);

    /**
     * @param int $templateId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($templateId);
}
