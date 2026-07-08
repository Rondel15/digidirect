<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class MadMimi extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
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
            'dob' => 'birthdate',
            'gender' => 'gender',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'phone',
            'fax' => 'fax',
            'company' => 'company',
            'street' => 'street',
            'city' => 'city',
            'country_id' => 'country',
            'region' => 'state',
            'postcode' => 'zip',
            'coupon' => '',
        ];
    }
}
