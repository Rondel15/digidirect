<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class Klaviyo extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'email',
            'firstname' => 'first_name',
            'middlename' => '',
            'lastname' => 'last_name',
            'suffix' => '',
            'dob' => '',
            'gender' => '',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'phone_number',
            'fax' => '',
            'company' => 'organization',
            'street' => '',
            'city' => '',
            'country_id' => '',
            'region' => '',
            'postcode' => '',
            'coupon' => ''
        ];
    }
}
