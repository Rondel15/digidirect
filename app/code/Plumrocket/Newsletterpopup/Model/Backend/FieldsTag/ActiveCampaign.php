<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class ActiveCampaign extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
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
            'telephone' => 'phone',
            'fax' => '',
            'company' => 'orgname',
            'street' => '',
            'city' => '',
            'country_id' => '',
            'region' => '',
            'postcode' => '',
            'coupon' => '',
        ];
    }
}
