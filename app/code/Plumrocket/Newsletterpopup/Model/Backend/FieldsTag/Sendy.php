<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class Sendy extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'email',
            'name' => 'first_name',
            'country' => 'country',
            'ipaddress' => '',
            'referrer' => '',
            'gdpr' => '',
            'silent' => '',
            'hp' => '',
            'boolean' => false
        ];
    }
}
