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
interface BuildInThemeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get theme list.
     *
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface[]
     */
    public function getItems();

    /**
     * Set theme list.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
