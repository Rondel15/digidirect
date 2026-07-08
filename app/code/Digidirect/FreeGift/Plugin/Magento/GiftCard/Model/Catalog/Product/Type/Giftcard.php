<?php

namespace Digidirect\FreeGift\Plugin\Magento\GiftCard\Model\Catalog\Product\Type;

use Magento\GiftCard\Model\Catalog\Product\Type\Giftcard as OriginalGiftcard;
use Digidirect\FreeGift\Helper\GiftCard as GiftCardHelper;
use Digidirect\FreeGift\Helper\InfoBuyRequestSerializer;

class Giftcard
{
    /**
     * @var GiftCardHelper
     */
    protected $_giftCardHelper;

    /**
     * Serializer interface instance.
     *
     * @var InfoBuyRequestSerializer
     */
    protected $_infoBuyRequestSerializer;

    /**
     * Giftcard constructor.
     * @param GiftCardHelper $giftCardHelper
     * @param InfoBuyRequestSerializer $infoBuyRequestSerializer
     */
    public function __construct(
        GiftCardHelper $giftCardHelper,
        InfoBuyRequestSerializer $infoBuyRequestSerializer
    ) {
        $this->_giftCardHelper = $giftCardHelper;
        $this->_infoBuyRequestSerializer = $infoBuyRequestSerializer;
    }

    /**
     * @param OriginalGiftcard $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param null|string $processMode
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundPrepareForCartAdvanced(
        OriginalGiftcard $subject,
        \Closure $proceed,
        $buyRequest,
        $product,
        $processMode
    ) {
        if ($product->getData(\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY) === false
            && in_array($processMode, [null, \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL], true)
            && !$buyRequest->getGiftcardAmount()
        ) {
            $amount = $this->_giftCardHelper->getFreeGiftGiftcardAmount($product);
            if ($amount > 0) {
                $buyRequest->setGiftcardAmount($amount);
                $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_LITE;
            }
        }
        return $proceed($buyRequest, $product, $processMode);
    }

    /**
     * @param OriginalGiftcard $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundHasRequiredOptions(OriginalGiftcard $subject, \Closure $proceed, $product)
    {
        if ($product->getData('_key_for_check_product_availability_to_auto_add')) {
            $amount = $this->_giftCardHelper->getFreeGiftGiftcardAmount($product);
            if ($amount > 0) {
                return false;
            }
        }
        return $proceed($product);
    }

    /**
     * @param OriginalGiftcard $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return OriginalGiftcard
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckProductBuyState(OriginalGiftcard $subject, \Closure $proceed, $product)
    {
        $option = $product->getCustomOption('info_buyRequest');
        if ($option instanceof \Magento\Quote\Model\Quote\Item\Option) {
            $data = $this->_infoBuyRequestSerializer->unserialize($option->getValue());
            if (isset($data['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY])
                && $data['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY] === false
            ) {
                return $subject;
            }
        }
        return $proceed($product);
    }
}
