<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Devices extends Base
{
    const ALL      = 'all';
    const DESKTOP  = 'desktop';
    const TABLET   = 'tablet';
    const MOBILE   = 'mobile';

    public function toOptionHash()
    {
        return [
            // self::ALL      => __('All Devices'),
            self::DESKTOP  => __('Desktop'),
            self::TABLET   => __('Tablet'),
            self::MOBILE   => __('Mobile'),
        ];
    }
}
