<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Api;

/**
 * Retrieve the lasted data of build-in themes
 *
 * Contains name, html and css code, default configuration
 * Used for recurring themes and simplify update process
 *
 * @since 4.0.0
 */
interface PopupBuildInThemeRegistryInterface
{
    /**
     * Get popups.
     *
     * @param bool $serialized
     * @return array[]
     */
    public function getList(bool $serialized = false): array;

    /**
     * Get theme properties.
     *
     * @param string $identifier
     * @param bool   $serialized
     * @return array
     */
    public function getThemeData(string $identifier, bool $serialized = false): array;

    /**
     * Get new id for old theme.
     *
     * @param int $themeId
     * @return mixed
     */
    public function getIdentifierForOldTheme(int $themeId): string;
}
