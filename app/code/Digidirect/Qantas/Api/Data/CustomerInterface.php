<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\Qantas\Api\Data;

use Magento\Customer\Api\Data\CustomerInterface as MainCustomerInterface;
/**
 * Customer interface.
 * @api
 * @since 100.0.2
 */
interface CustomerInterface extends MainCustomerInterface
{
    const QFF_LASTNAME = 'qff_lastname';
    const QFF_NUMBER = 'qff_number';

    public function getQffNumber();
    
    public function setQffLastname($qff_lastname);
    
    public function getQffLastname();
    
    public function setQffNumber($qff_number);

}
