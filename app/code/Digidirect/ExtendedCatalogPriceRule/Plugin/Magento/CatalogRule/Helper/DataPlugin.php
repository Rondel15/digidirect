<?php
namespace Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Helper;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Magento\CatalogRule\Helper\Data;

/**
 * Class DataPlugin
 * @package Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Helper
 */
class DataPlugin
{
    /**
     * @param Data $subject
     * @param float|int $result
     * @param string $actionOperator
     * @param int $ruleAmount
     * @param float $price
     * @return float|int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCalcPriceRule(Data $subject, $result, $actionOperator, $ruleAmount, $price)
    {
        switch ($actionOperator) {
            case RuleDisplayMessageInterface::ACTION_CODE:
                return $price;
            default:
                return $result;
        }
    }
}
