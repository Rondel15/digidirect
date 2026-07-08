<?php

namespace Digidirect\Feed\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface FeedRepositoryInterface
{
    /**
     * @param Data\FeedInterface $feed
     * @return Data\FeedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\FeedInterface $feed);

    /**
     * @param int $feedId
     * @return Data\FeedInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($feedId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\FeedSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\FeedInterface $feed
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\FeedInterface $feed);

    /**
     * @param int $feedId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($feedId);
}
