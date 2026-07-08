<?php

namespace Digidirect\FreeGift\Helper;

use Magento\Framework\App\Helper\AbstractHelper as AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class GiftCard extends AbstractHelper
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var InfoBuyRequestSerializer
     */
    protected $_infoBuyRequestSerializer;

    /**
     * Data constructor.
     * @param Context $context
     * @param PriceCurrencyInterface $priceCurrency
     * @param InfoBuyRequestSerializer $infoBuyRequestSerializer
     */
    public function __construct(
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        InfoBuyRequestSerializer $infoBuyRequestSerializer
    ) {
        $this->_priceCurrency = $priceCurrency;
        $this->_infoBuyRequestSerializer = $infoBuyRequestSerializer;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return null|float
     */
    public function getFreeGiftGiftcardAmount($product)
    {
        $minAmount = null;
        foreach ($product->getGiftcardAmounts() as $value) {
            $amount = $this->_priceCurrency->round($value['website_value']);
            if ($amount <= 0) {
                continue;
            }
            if ($minAmount === null || $amount < $minAmount) {
                $minAmount = $amount;
            }
        }
        return $minAmount;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem
     * @param array $giftcardOptions
     * @return $this
     */
    public function setGiftCardOptionsToQuoteItem(
        \Magento\Quote\Model\Quote\Item\AbstractItem $quoteItem,
        array $giftcardOptions
    ) {
        $product = $quoteItem->getProduct();
        $buyRequestOption = $quoteItem->getOptionByCode('info_buyRequest');
        $buyRequestData = $this->_infoBuyRequestSerializer->unserialize($buyRequestOption->getValue());

        $hasChanges = false;
        foreach ($giftcardOptions as $optionCode => $optionValue) {
            if (!array_key_exists($optionCode, $buyRequestData)) {
                $hasChanges = true;
                break;
            }
            if ($buyRequestData[$optionCode] !== $optionValue) {
                $hasChanges = true;
                break;
            }
        }

        $buyRequestData = array_merge($buyRequestData, $giftcardOptions);
        $buyRequestOption->setValue($this->_infoBuyRequestSerializer->serialize($buyRequestData));

        foreach ($giftcardOptions as $optionCode => $optionValue) {
            $option = $quoteItem->getOptionByCode($optionCode);
            if ($option instanceof \Magento\Quote\Model\Quote\Item\Option) {
                $option->setValue($optionValue);
                continue;
            }

            $option = new \Magento\Framework\DataObject();
            $option->setValue($optionValue);
            $option->setCode($optionCode);
            $option->setProduct($product);
            $quoteItem->addOption($option);
        }

        if ($hasChanges) {
            /**
             * magento bug:
             * if we will change ONLY custom options (for example only VALUE of custom option) for quote item
             * then save item method will not handle custom option changes
             */
            $quoteItem->setUpdatedAt(null);
        }

        return $this;
    }
}
