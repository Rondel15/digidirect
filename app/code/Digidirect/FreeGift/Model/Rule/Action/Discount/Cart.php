<?php

namespace Digidirect\FreeGift\Model\Rule\Action\Discount;

use Digidirect\FreeGift\Api\Data\RuleInterface;

class Cart extends AbstractDiscount
{
    /**
     * @param RuleInterface $freeGiftRule
     * @return bool
     */
    protected function _isFreeGiftHidden(RuleInterface $freeGiftRule)
    {
        return (bool)$freeGiftRule->isHiddenForCustomer();
    }
}
