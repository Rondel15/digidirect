<?php

namespace Digidirect\FreeGift\Plugin\Magento\Quote\Model\Quote\Item;

use Digidirect\FreeGift\Helper\GiftCard as GiftCardHelper;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Address\Item as QuoteAddressItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem as OriginalToOrderItem;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

/**
 * Class ToOrderItem
 * @package Digidirect\FreeGift\Plugin\Magento\Quote\Model\Quote\Item
 */
class ToOrderItem
{
    /**
     * @var \Digidirect\FreeGift\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Digidirect\FreeGift\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var GiftCardHelper
     */
    protected $_giftCardHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Serializer interface instance.
     *
     * @var SerializerJson
     */
    protected $_serializer;

    /**
     * ToOrderItem constructor.
     * @param \Digidirect\FreeGift\Helper\Data $dataHelper
     * @param \Digidirect\FreeGift\Helper\Config $configHelper
     * @param GiftCardHelper $giftCardHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param SerializerJson $serializer
     */
    public function __construct(
        \Digidirect\FreeGift\Helper\Data $dataHelper,
        \Digidirect\FreeGift\Helper\Config $configHelper,
        GiftCardHelper $giftCardHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        SerializerJson $serializer
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_configHelper = $configHelper;
        $this->_giftCardHelper = $giftCardHelper;
        $this->_scopeConfig = $scopeConfig;
        $this->_serializer = $serializer;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\ToOrderItem $subject
     * @param \Closure $proceed
     * @param QuoteItem|QuoteAddressItem $quoteItem
     * @param array $data
     * @return OrderItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundConvert(OriginalToOrderItem $subject, \Closure $proceed, $quoteItem, $data = [])
    {
        $orderItem = $proceed($quoteItem, $data);
        if ($this->_dataHelper->isFreeGiftItem($quoteItem) &&
            $orderItem->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
        ) {
            if ($additional = $quoteItem->getOptionByCode('additional_options')) {
                if ($additionalFromOrder = $orderItem->getProductOptionByCode('additional_options')) {
                    $additional = array_merge($additional, $additionalFromOrder);
                }
                /*if (count($additional) > 0) {
                    $options = $orderItem->getProductOptions();
                    $options['additional_options'] = $this->_serializer->unserialize($additional->getValue());
                    $orderItem->setProductOptions($options);
                }*/
            }
        }
        return $orderItem;
    }

    /**
     * @param OriginalToOrderItem $subject
     * @param QuoteItem|QuoteAddressItem $item
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeConvert(OriginalToOrderItem $subject, $item, $data = [])
    {
        /**
         * @var $quoteItem QuoteItem
         */
        $quoteItem = $item instanceof QuoteAddressItem ?
            $item->getQuote()->getItemById($item->getQuoteItemId()) : $item;

        $product = $quoteItem->getProduct();
        if ($product->getTypeId() != 'giftcard') {
            return;
        }

        $buyRequest = $quoteItem->getBuyRequest();
        $buyRequestData = $buyRequest->getData();
        if (!isset($buyRequestData['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY])
            || $buyRequestData['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY] !== false
        ) {
            return;
        }

        $quote = $quoteItem->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $email = $quote->getCustomerEmail() ? $quote->getCustomerEmail() : $billingAddress->getEmail();

        $senderName = $this->_scopeConfig->getValue(
            'trans_email/ident_sales/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
        $senderEmail = $this->_scopeConfig->getValue(
            'trans_email/ident_sales/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $quote->getStoreId()
        );
        $giftcardOptions = [
            'giftcard_sender_name' => $senderName,
            'giftcard_recipient_name' => $billingAddress->getName(),
            'giftcard_sender_email' => $senderEmail,
            'giftcard_recipient_email' => $email,
            'giftcard_message' => null,
        ];
        $this->_giftCardHelper->setGiftCardOptionsToQuoteItem($quoteItem, $giftcardOptions);
    }
}
