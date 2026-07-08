<?php
namespace Digidirect\Collect\Model;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractApplyShippingVariation
 * @package Digidirect\Collect\Model
 */
class AbstractApplyShippingVariation
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractApplyShippingVariation constructor.
     * @param Session $session
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session $session,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $session;
        $this->logger = $logger;
    }

    /**
     * @param string|null $collectPlaceId
     * @param string|null $storageName
     * @return bool|Item[]
     */
    public function applyCollectParamsToAllItems($collectPlaceId = null, $storageName = null)
    {
        // $collectPlaceId and $storageName must be both NULL or both string.
        if ((empty($collectPlaceId) && !empty($storageName))
            || (!empty($collectPlaceId) && empty($storageName))) {
            return false;
        }

        $this->checkoutSession->setCollectPlaceId($collectPlaceId);
        $this->checkoutSession->setCollectPlaceStorageName($storageName);

        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        /** @var Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $quoteItem->setCollectPlaceId($collectPlaceId);
            $quoteItem->setCollectPlaceStorageName($storageName);
        }
        try {
            $this->checkoutSession->getQuote()->collectTotals();
            $this->checkoutSession->getQuote()->save();
        } catch (\Exception $e) {
            return false;
        }
        return $quoteItems;
    }
}
