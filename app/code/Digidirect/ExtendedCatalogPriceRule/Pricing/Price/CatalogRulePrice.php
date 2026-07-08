<?php
namespace Digidirect\ExtendedCatalogPriceRule\Pricing\Price;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogRulePrice extends \Magento\CatalogRuleStaging\Pricing\Price\CatalogRulePrice
{
    /**
     * {@inheritdoc}
     */
    protected function calculateRuleProductPrice(\Magento\CatalogRule\Model\Rule $rule, $currentProductPrice)
    {
        switch ($rule->getSimpleAction()) {
            case RuleDisplayMessageInterface::ACTION_CODE:
                return $this->priceCurrency->round($currentProductPrice);
            default:
                return parent::calculateRuleProductPrice($rule, $currentProductPrice);
        }
    }
}
