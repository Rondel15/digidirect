<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class ConstantContact extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'address',
            'firstname' => 'first_name',
            'middlename' => '',
            'lastname' => 'last_name',
            'suffix' => '',
            'dob' => 'birthday',
            'gender' => '',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'phone_number',
            'fax' => '',
            'company' => 'company_name',
            'street' => 'street',
            'city' => 'city',
            'country_id' => 'country',
            'region' => 'state',
            'postcode' => '',
            'coupon' => ''
        ];
    }
}
