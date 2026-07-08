<?php

namespace Digidirect\Feed\Export\Filter;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\Feed\Export\Context;
use Magento\Tax\Model\Calculation as TaxCalculation;

class CurrencyFilter
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * CurrencyFilter constructor.
     * @param Context $context
     * @param DirectoryHelper $directoryHelper
     * @param StoreManagerInterface $storeManager
     * @param TaxCalculation $taxCalculation
     */
    public function __construct(
        Context $context,
        DirectoryHelper $directoryHelper,
        StoreManagerInterface $storeManager,
        TaxCalculation $taxCalculation
    ) {
        $this->context = $context;
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * Convert
     *
     * Convert price from base store currency to 'x' currency.
     *
     * @param string $input
     * @param string $toCurrency
     * @return string
     */
    public function convert($input, $toCurrency)
    {
        $value = floatval($input);

        return $this->directoryHelper->currencyConvert(
            $value,
            $this->storeManager->getStore()->getBaseCurrencyCode(),
            $toCurrency
        );
    }

    /**
     * Include Tax
     *
     * Add tax to product price
     *
     * @param float $price
     * @return float
     */
    public function inclTax($price)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->context->getCurrentObject();

        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->context->getFeed()->getStoreId());
        $request->setData('product_class_id', $product->getData('tax_class_id'));

        return $price + $this->taxCalculation->calcTaxAmount(
            $price,
            $this->taxCalculation->getRate($request),
            false,
            false
        );
    }

    /**
     * Exclude Tax
     *
     * Exclude tax to product price
     *
     * @param float $price
     * @return float
     */
    public function exclTax($price)
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->context->getCurrentObject();

        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->context->getFeed()->getStoreId());
        $request->setData('product_class_id', $product->getData('tax_class_id'));

        return $price - $this->taxCalculation->calcTaxAmount(
            $price,
            $this->taxCalculation->getRate($request),
            true,
            false
        );
    }
}
