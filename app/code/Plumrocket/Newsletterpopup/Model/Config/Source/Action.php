<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class Action extends Base
{
    const CANCEL     = 'cancelled';
    const SUBSCRIBE = 'subscribed';
    const OTHER     = 'other';

    public function toOptionHash()
    {
        return [
            self::CANCEL     => __('Cancelled'),
            self::SUBSCRIBE => __('Subscribed'),
            self::OTHER     => __('Other'),
        ];
    }
}
