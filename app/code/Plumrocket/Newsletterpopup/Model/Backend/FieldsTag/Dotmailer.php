<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class Dotmailer extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'EMAIL',
            'firstname' => 'FIRSTNAME',
            'middlename' => '',
            'lastname' => 'LASTNAME',
            'suffix' => '',
            'dob' => '',
            'gender' => 'GENDER',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => '',
            'fax' => '',
            'company' => '',
            'street' => '',
            'city' => '',
            'country_id' => '',
            'region' => '',
            'postcode' => 'POSTCODE',
            'coupon' => '',
        ];
    }
}
