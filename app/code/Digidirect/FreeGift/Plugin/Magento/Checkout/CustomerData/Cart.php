<?php

namespace Digidirect\FreeGift\Plugin\Magento\Checkout\CustomerData;

use Magento\Checkout\CustomerData\ItemPoolInterface;
use Magento\Checkout\Model\Session;
use Digidirect\FreeGift\Helper\Data;

/**
 * Class Cart
 * @package Digidirect\FreeGift\Plugin\Magento\Checkout\CustomerData
 */
class Cart
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var ItemPoolInterface
     */
    protected $itemPoolInterface;

    /**
     * Cart constructor.
     * @param Data $helper
     * @param Session $checkoutSession
     * @param ItemPoolInterface $itemPoolInterface
     */
    public function __construct(
        Data $helper,
        Session $checkoutSession,
        ItemPoolInterface $itemPoolInterface
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->itemPoolInterface = $itemPoolInterface;
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        if (empty($result['items'])) {
            return $result;
        }
        $allItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($allItems as $key => $item) {
            try {
                if ($this->helper->isFreeGiftItem($item) &&
                    !$this->isGiftExistsInResult($item, $result['items'])) {
                    $result['items'][] = $this->itemPoolInterface->getItemData($item);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        return $result;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $results
     * @return bool
     */
    protected function isGiftExistsInResult(\Magento\Quote\Model\Quote\Item $item, array $results)
    {
        foreach ($results as $resultItem) {
            if (!empty($resultItem['item_id']) && $resultItem['item_id'] === $item->getId()) {
                return true;
            }
            continue;
        }
        return false;
    }
}
