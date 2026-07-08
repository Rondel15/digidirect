<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class Egoi extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'email',
            'firstname' => 'first_name',
            'middlename' => 'first_name',
            'lastname' => 'last_name',
            'suffix' => '',
            'dob' => 'birth_date',
            'gender' => 'gender',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'telephone',
            'fax' => 'fax',
            'company' => 'company',
            'street' => 'street',
            'city' => 'city',
            'country_id' => 'country',
            'region' => 'state',
            'postcode' => 'postal_code',
            'coupon' => ''
        ];
    }
}
