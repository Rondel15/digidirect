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
interface PopupThemeInterface extends TemplateInterface
{
    const IDENTIFIER = 'identifier';
    const HTML = 'code';
    const CSS = 'style';
    const NAME = 'name';
    const DEFAULT_CONFIGURATION = 'default_values';

    /**
     * @return bool
     */
    public function isBuildIn(): bool;

    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param string $identifier
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface
     */
    public function setIdentifier(string $identifier): PopupThemeInterface;

    /**
     * @return string
     */
    public function getDefaultConfiguration(): string;

    /**
     * @param string $defaultConfiguration
     * @return \Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface
     */
    public function setDefaultConfiguration(string $defaultConfiguration): PopupThemeInterface;
}
