<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Thumbnail;

/**
 * @since 4.0.0
 */
class Generator extends AbstractGenerator
{
    const TYPE = 'popup';

    public function getImagePath(int $entityId, bool $forWeb = false): string
    {
        return $this->webOrDirFormat(
            $forWeb,
            $this->thumbnailPath . "popup_{$entityId}.png"
        );
    }

    public function getImageCachePath(int $entityId, bool $forWeb = false): string
    {
        return $this->webOrDirFormat(
            $forWeb,
            $this->thumbnailPath . $this->cacheSubDirectory . "popup_{$entityId}.png"
        );
    }
}
