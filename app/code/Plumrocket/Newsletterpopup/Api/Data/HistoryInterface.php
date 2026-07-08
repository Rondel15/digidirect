<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api\Data;

/**
 * @since v4.6.0
 */
interface HistoryInterface
{

    public const ACTION = 'action';
    public const POPUP_ID = 'popup_id';

    /**
     * Set action.
     *
     * @param string $action
     * @return \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface
     */
    public function setAction(string $action): HistoryInterface;

    /**
     * Set popup id.
     *
     * @param int $popupId
     * @return \Plumrocket\Newsletterpopup\Api\Data\HistoryInterface
     */
    public function setPopupId(int $popupId): HistoryInterface;
}
