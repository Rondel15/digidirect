<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model;

/**
 * Registry for current state
 *
 * @since 4.0.0
 */
class Preview
{

    /**
     * @var bool
     */
    private $previewMode = false;

    /**
     * Check if we are in preview
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->previewMode;
    }

    /**
     * Set preview mode.
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsPreviewMode(bool $flag): Preview
    {
        $this->previewMode = $flag;
        return $this;
    }
}
