<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api;

use Plumrocket\Newsletterpopup\Api\Data\HistoryInterface;

/**
 * @since 4.6.0
 */
interface HistoryRepositoryInterface
{

    /**
     * Get history by its id.
     *
     * @param int  $historyId
     * @param bool $forceReload
     * @return \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $historyId, bool $forceReload = false): HistoryInterface;

    /**
     * Create or update popup
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface $history
     * @return \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(HistoryInterface $history): HistoryInterface;
}
