<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api;

use Plumrocket\Newsletterpopup\Api\Data\HistoryInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;

/**
 * @since 4.6.0
 */
interface HistoryManagementInterface
{

    /**
     * Save record of subscription canceling.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface|null
     */
    public function logCanceling(PopupInterface $popup): ?HistoryInterface;
}
