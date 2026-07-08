<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api\Data;

use Plumrocket\Newsletterpopup\Model\Popup\TemplateInterface;

/**
 * @since v4.0.0
 */
interface PopupInterface extends TemplateInterface
{
    public const USE_CURRENT_PRODUCT = 'use_current_product';
    public const DEFAULT_PRODUCT = 'default_product';
    public const TYPE = 'type';
    public const NAME = 'name';
    public const HTML = 'code';
    public const CSS = 'style';
    public const SUCCESS_PAGE = 'success_page';
    public const CUSTOM_SUCCESS_PAGE = 'custom_success_page';

    /**
     * Check if popup is enabled.
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return bool
     */
    public function isModal(): bool;

    /**
     * Retrieve whether popup in modal or used for widget
     *
     * @return string
     */
    public function getType(): string;

    /**
     * @param string $type
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function setType(string $type): PopupInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function setName(string $name): PopupInterface;

    /**
     * @param string $sku
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function setDefaultProduct(string $sku): PopupInterface;

    /**
     * @return bool
     */
    public function useCurrentProduct(): bool;

    /**
     * @param bool $flag
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupInterface
     */
    public function setUseCurrentProduct(bool $flag): PopupInterface;

    /**
     * @return string
     */
    public function getSuccessPage(): string;

    /**
     * @return string
     */
    public function getCustomSuccessPage(): string;

    /**
     * Get event on which popup should be showed.
     *
     * @return string
     * @see \Plumrocket\Newsletterpopup\Model\Config\Source\Method - all options listed here
     */
    public function getDisplayPopup(): string;

    /**
     * Get delay time to use instead of leave event for mobile.
     *
     * @return int
     */
    public function getMobileLeaveDelayTime(): int;

    /**
     * Get delay time.
     *
     * @return int
     */
    public function getDelayTime(): int;
}
