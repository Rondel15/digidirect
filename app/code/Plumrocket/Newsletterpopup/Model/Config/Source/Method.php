<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

use Plumrocket\Base\Model\OptionSource\AbstractSource;

class Method extends AbstractSource
{
    public const AFTER_TIME_DELAY  = 'after_time_delay';
    public const LEAVE_SITE        = 'leave_page';
    public const PAGE_SCROLL       = 'on_page_scroll';
    public const MOUSEOVER         = 'on_mouseover';
    public const CLICK             = 'on_click';
    public const MANUALLY          = 'manually';

    /**
     * Get display popup methods.
     *
     * @return array
     */
    public function toOptionHash(): array
    {
        return [
            self::AFTER_TIME_DELAY  => __('After the time delay'),
            self::LEAVE_SITE        => __('When leaving the site (out of focus)'),
            self::PAGE_SCROLL       => __('On Page Scroll'),
            self::MOUSEOVER         => __('On Mouse Over'),
            self::CLICK             => __('On Click'),
            self::MANUALLY          => __('Manually (for web developers)'),
        ];
    }
}
