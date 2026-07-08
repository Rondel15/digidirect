<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend\FieldsTag;

class SalesForce extends \Plumrocket\Newsletterpopup\Model\Backend\AbstractFieldsTag
{
    /**
     * @return array
     */
    public function getFields()
    {
        return [
            'email' => 'email',
            'firstname' => 'FirstName',
            'middlename' => 'MiddleName',
            'lastname' => 'LastName',
            'suffix' => '',
            'dob' => 'Birthdate',
            'gender' => 'gender',
            'taxvat' => '',
            'prefix' => '',
            'telephone' => 'Phone',
            'fax' => 'fax',
            'company' => '',
            'street' => 'MailingStreet',
            'city' => 'MailingCity',
            'country_id' => 'MailingCountry',
            'region' => 'MailingState',
            'postcode' => 'MailingPostalCode',
            'coupon' => '',
        ];
    }
}
