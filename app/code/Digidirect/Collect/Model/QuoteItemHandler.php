<?php

namespace Digidirect\Collect\Model;

use Digidirect\Collect\Helper\Data as CollectHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Model\Session;

class QuoteItemHandler
{
    /**
     * Collect Helper
     *
     * @var \Digidirect\Collect\Helper\Data
     */
    protected $_collectHelper;

    /**
     * CollectQuantityValidator
     *
     * @var \Digidirect\Collect\Model\CollectQuantityValidator
     */
    protected $_collectQuantityValidator;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * QuoteItemHandler constructor.
     *
     * @param CollectQuantityValidator $collectQuantityValidator
     * @param CollectHelper $collectHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        \Digidirect\Collect\Model\CollectQuantityValidator $collectQuantityValidator,
        \Digidirect\Collect\Helper\Data $collectHelper,
        Session $checkoutSession = null
    ) {
        $this->_collectHelper = $collectHelper;
        $this->_collectQuantityValidator = $collectQuantityValidator;
        $this->checkoutSession = $checkoutSession ?: ObjectManager::getInstance()->get(Session::class);
    }

    /**
     * ChangeQuoteItemType
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $deliveryType
     * @param string $collectPlaceId
     * @param string $collectPlaceStorageName
     * @return void
     */
    public function changeQuoteItemCollectType($quoteItem, $deliveryType, $collectPlaceId, $collectPlaceStorageName)
    {
        if ($deliveryType == CollectHelper::DELIVERY_TYPE_COLLECT &&
            $collectPlaceId &&
            $collectPlaceStorageName
        ) {
            $quoteItem->setCollectPlaceId($collectPlaceId);
            $quoteItem->setCollectPlaceStorageName($collectPlaceStorageName);
        } elseif ($deliveryType == CollectHelper::DELIVERY_TYPE_DELIVER) {
            $quoteItem->setCollectPlaceId(null);
            $quoteItem->setCollectPlaceStorageName(null);
        } elseif (!empty($this->checkoutSession->getCollectPlaceId())
            && !empty($this->checkoutSession->getCollectPlaceStorageName())
        ) {
            $quoteItem->setCollectPlaceId($this->checkoutSession->getCollectPlaceId());
            $quoteItem->setCollectPlaceStorageName($this->checkoutSession->getCollectPlaceStorageName());
        }
    }

    /**
     * CheckQty
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @param string $collectPlaceId
     * @param string $collectPlaceStorageName
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function checkQty($quoteItem, $collectPlaceId, $collectPlaceStorageName)
    {
        $result = true;
        $oldCollectPlaceId = $quoteItem->getCollectPlaceId();
        $oldCollectPlaceStorageName = $quoteItem->getCollectPlaceStorageName();
        if (!$quoteItem->getCollectPlaceId() && !$quoteItem->getCollectPlaceStorageName()) {
            $quoteItem->setCollectPlaceId($collectPlaceId);
            $quoteItem->setCollectPlaceStorageName($collectPlaceStorageName);
        }

        if ($this->_collectHelper->hasStockUpdateInterface() &&
            (($quoteItem->getCollectPlaceId() && $quoteItem->getCollectPlaceStorageName()))
        ) {
            try {
                if ($collectPlaceId === null) {
                    $collectPlaceId = $quoteItem->getCollectPlaceId();
                }
                $checkQty = $this->_collectQuantityValidator->checkProductQtyInSource($quoteItem, $collectPlaceId);
            } catch (\Exception $e) {
                $this->_collectHelper->logError($e->getMessage());
                $quoteItem->setUseOldQty(true);
                $result = false;
            }

            if (empty($checkQty)) {
                $quoteItem->setUseOldQty(true);
                $result = false;
            }
        }

        $quoteItem->setCollectPlaceId($oldCollectPlaceId);
        $quoteItem->setCollectPlaceStorageName($oldCollectPlaceStorageName);

        return $result;
    }
}
