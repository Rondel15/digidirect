<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * @since 4.0.0
 */
interface PopupSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get popup list.
     *
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface[]
     */
    public function getItems();

    /**
     * Set popup list.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
