<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Erase extends Base
{
    const WEEK_1 = 7;
    const WEEK_2 = 14;
    const WEEK_3 = 21;
    const MON_1 = 30;
    const MON_3 = 91;
    const MON_6 = 182;
    const MON_12 = 365;
    const MON_0 = 0;

    public function toOptionHash()
    {
        return [
            self::WEEK_1   => __('Older than 1 week'),
            self::WEEK_2   => __('Older than 2 weeks'),
            self::WEEK_3   => __('Older than 3 weeks'),
            self::MON_1    => __('Older than 1 month'),
            self::MON_3    => __('Older than 3 months'),
            self::MON_6    => __('Older than 6 months'),
            self::MON_12   => __('Older than 1 year'),
            self::MON_0    => __('Never'),
        ];
    }
}
