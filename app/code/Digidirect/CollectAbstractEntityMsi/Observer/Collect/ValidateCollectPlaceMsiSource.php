<?php
namespace Digidirect\CollectAbstractEntityMsi\Observer\Collect;

use Digidirect\Collect\Api\Data\CollectPlaceInterface;
use Digidirect\Collect\Helper\Data;
use Digidirect\Collect\Model\AddToCart\CollectException;
use Digidirect\CollectAbstractEntityMsi\Model\MsiAvailability;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote\Item;

/**
 * Class ValidateCollectPlaceMsiSource
 * @package Digidirect\CollectAbstractEntityMsi\Observer\Collect
 */
class ValidateCollectPlaceMsiSource implements ObserverInterface
{
    /**
     * @var MsiAvailability
     */
    protected $msiAvailability;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * ValidateCollectPlaceMsiSource constructor.
     * @param MsiAvailability $msiAvailability
     * @param Data $helper
     */
    public function __construct(
        MsiAvailability $msiAvailability,
        Data $helper
    ) {
        $this->msiAvailability = $msiAvailability;
        $this->helper = $helper;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws CollectException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isSingleCartVariation()) { // @todo Can be removed in the future requests
            return;
        }
        $items = $observer->getEvent()->getData('quote_items');
        /** @var CollectPlaceInterface $place */
        $place = $observer->getEvent()->getData('collect_place');

        $skuQty = [];
        /** @var Item $item */
        foreach ($items as $item) {
            $skuQty[$item->getSku()] = $item->getQty();
        }

        $sourceItems = $this->msiAvailability->prepareSourceDataForItems(array_keys($skuQty));
        $available = true; // Andrew requested that all store is selectable; $this->msiAvailability->isPlaceAvailable($place, $sourceItems, $skuQty);

        if (!$available) {
            throw new CollectException(
                __("Items are not available in MSI Sources for Collect Place '%1'", $place->getName())
            );
        }
    }
}
