<?php

namespace Digidirect\ExtendedCartPriceRules\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Interface RuleManagementInterface
 * @package Digidirect\ExtendedCartPriceRules\Api
 */
interface RuleManagementInterface
{
    /**
     * @param int|null $cartId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodLimit($cartId = null);
}
