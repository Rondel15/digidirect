<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup\Variable;

use Plumrocket\Newsletterpopup\Api\Data\PopupInterface;
use Plumrocket\Newsletterpopup\Api\Data\PopupThemeInterface;

/**
 * @since 4.0.0
 */
class Placeholder
{
    /**
     * @param string                             $placeholder
     * @param PopupInterface|PopupThemeInterface $popup
     * @return bool
     */
    public function hasPlaceholder(string $placeholder, $popup): bool
    {
        return false !== mb_strpos($popup->getHtml(), $placeholder);
    }

    /**
     * @param PopupInterface|PopupThemeInterface $popup
     * @return bool
     */
    public function hasProductPlaceholder($popup): bool
    {
        return $this->hasPlaceholder('{{product_', $popup);
    }
}
