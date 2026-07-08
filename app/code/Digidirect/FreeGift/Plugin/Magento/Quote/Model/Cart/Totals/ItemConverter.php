<?php

namespace Digidirect\FreeGift\Plugin\Magento\Quote\Model\Cart\Totals;

/**
 * Class ItemConverter
 * @package Digidirect\FreeGift\Plugin\Magento\Quote\Model\Cart\Totals
 */
class ItemConverter
{
    const ITEM_TOTALS_FREE_GIFT_ITEM_FLAG = 'digidirect_is_free_gift_item';
    const ITEM_TOTALS_FREE_GIFT_ITEM_PRICE = 'digidirect_free_gift_price';

    /**
     * @var \Digidirect\FreeGift\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Digidirect\FreeGift\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var \Magento\Quote\Api\Data\TotalsItemExtensionFactory
     */
    protected $_totalsItemExtensionFactory;

    /**
     * ItemConverter constructor.
     * @param \Digidirect\FreeGift\Helper\Data $dataHelper
     * @param \Digidirect\FreeGift\Helper\Config $configHelper
     * @param \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalsItemExtensionFactory
     */
    public function __construct(
        \Digidirect\FreeGift\Helper\Data $dataHelper,
        \Digidirect\FreeGift\Helper\Config $configHelper,
        \Magento\Quote\Api\Data\TotalsItemExtensionFactory $totalsItemExtensionFactory
    ) {
        $this->_dataHelper = $dataHelper;
        $this->_configHelper = $configHelper;
        $this->_totalsItemExtensionFactory = $totalsItemExtensionFactory;
    }

    /**
     * Insert an additional data to Totals item to mark a Free-gift product.
     *
     * @param \Magento\Quote\Model\Cart\Totals\ItemConverter $subject
     * @param \Closure $procede
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundModelToDataObject(
        \Magento\Quote\Model\Cart\Totals\ItemConverter $subject,
        \Closure $procede,
        \Magento\Quote\Model\Quote\Item $item
    ) {
        $result = $procede($item);

        if ($this->_dataHelper->isFreeGiftItem($item) && $this->_configHelper->isHideFreeItemPrice()) {
            $priceMessage = $this->_configHelper->getMessageForHiddenFreeItemPrice();

            $extensionAttributes = $item->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->_totalsItemExtensionFactory->create();
            }
            $extensionAttributes->setDigidirectIsFreeGiftItem(true);
            $extensionAttributes->setDigidirectFreeGiftPrice($priceMessage);
            $result->setExtensionAttributes($extensionAttributes);
        }

        return $result;
    }
}
