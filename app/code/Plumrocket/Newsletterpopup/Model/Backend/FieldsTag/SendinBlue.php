<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class SendinBlue extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'email',
            'firstname' => 'firstname',
            'middlename' => 'name',
            'lastname' => 'lastname',
            'suffix' => '',
            'dob' => 'birthdate',
            'gender' => 'gender',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'sms',
            'fax' => 'fax',
            'company' => 'company',
            'street' => 'street',
            'city' => 'city',
            'country_id' => 'country',
            'region' => 'state',
            'postcode' => 'postal_code',
            'coupon' => '',
        ];
    }
}
