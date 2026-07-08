<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2017 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Config\Source;

class SignupMethod extends Base
{
    const SIGNUP_ONLY         = 'signup_only';
    const SIGNUP_AND_REGISTER = 'register_signup';

    public function toOptionHash()
    {
        return [
            self::SIGNUP_ONLY         => __('Sign-up for email newsletter only'),
            self::SIGNUP_AND_REGISTER => __('Register customer account & sign-up for newsletter'),
        ];
    }
}
