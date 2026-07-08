<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class InfusionSoft extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'Email',
            'firstname' => 'FirstName',
            'middlename' => 'MiddleName',
            'lastname' => 'LastName',
            'suffix' => 'Suffix',
            'dob' => 'Birthday',
            'gender' => '',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'Phone1',
            'fax' => 'Fax1',
            'company' => 'Company',
            'street' => 'StreetAddress1',
            'city' => 'City',
            'country_id' => 'Country',
            'region' => 'State',
            'postcode' => 'PostalCode',
            'coupon' => '',
        ];
    }
}
