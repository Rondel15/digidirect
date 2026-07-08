<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;

/**
 * @since 4.0.0
 */
interface BuildInThemeRepositoryInterface
{
    /**
     * Save build-in theme.
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface $theme
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(PopupThemeInterface $theme): PopupThemeInterface;

    /**
     * Get all build-in themes.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Plumrocket\Newsletterpopup\Api\Data\BuildInThemeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): SearchResultsInterface;
}
