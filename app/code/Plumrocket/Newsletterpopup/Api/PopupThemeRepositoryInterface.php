<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2023 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Api;

use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;

/**
 * @since 4.6.0
 */
interface PopupThemeRepositoryInterface
{

    /**
     * Get popup theme by its id.
     *
     * @param int  $popupThemeId
     * @param bool $forceReload
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $popupThemeId, bool $forceReload = false): PopupThemeInterface;

    /**
     * Create or update popup
     *
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface $popupTheme
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(PopupThemeInterface $popupTheme): PopupThemeInterface;
}
