<?php
namespace Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Model\Indexer;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Magento\CatalogRule\Model\Indexer\ProductPriceCalculator;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class ProductPriceCalculatorPlugin
 * @package Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Model\Indexer
 */
class ProductPriceCalculatorPlugin
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param ProductPriceCalculator $subject
     * @param float $result productPrice
     * @param array $ruleData
     * @param null|array $productData
     * @return float
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCalculate(ProductPriceCalculator $subject, $result, $ruleData, $productData = null)
    {
        switch ($ruleData['action_operator']) {
            case RuleDisplayMessageInterface::ACTION_CODE:
                return $this->getOriginalPrice($ruleData, $productData);
            default:
                return $result;
        }
    }

    /**
     * Custom rule "Display Message" must not affect the price of the product.
     * @param array $ruleData
     * @param null|array $productData
     * @return float
     */
    protected function getOriginalPrice($ruleData, $productData)
    {
        if ($productData !== null && isset($productData['rule_price'])) {
            $productPrice = $productData['rule_price'];
        } else {
            $productPrice = $ruleData['default_price'];
        }
        return $this->priceCurrency->round($productPrice);
    }
}
