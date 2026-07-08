<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class HubSpot extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'email',
            'firstname' => 'firstname',
            'middlename' => '',
            'lastname' => 'lastname',
            'suffix' => '',
            'dob' => 'date_of_birth',
            'gender' => 'gender',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'phone',
            'fax' => 'fax',
            'company' => 'company',
            'street' => 'address',
            'city' => 'city',
            'country_id' => 'country',
            'region' => 'state',
            'postcode' => 'zip',
            'coupon' => '',
        ];
    }
}
