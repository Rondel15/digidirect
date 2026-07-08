<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;

/**
 * @since 4.0.0
 */
interface PopupRepositoryInterface
{
    /**
     * Get popups.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Get popup by is id.
     *
     * @param int  $popupId
     * @param bool $forceReload
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $popupId, bool $forceReload = false): PopupInterface;

    /**
     * Create or update popup
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(PopupInterface $popup): PopupInterface;

    /**
     * Delete popup
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(PopupInterface $popup): bool;

    /**
     * Delete popup by id.
     *
     * @param int $id
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($id): bool;
}
