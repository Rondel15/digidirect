<?php
namespace Digidirect\Collect\Plugin\Quote\Model;

use Magento\Quote\Model\Quote as Subject;
use Digidirect\Collect\Helper\Data as CollectHelper;

/**
 * Class Quote
 * @package Digidirect\Collect\Plugin\Quote\Model
 */
class Quote
{
    /**
     * @var CollectHelper
     */
    protected $collectHelper;

    /**
     * Quote constructor.
     * @param CollectHelper $collectHelper
     */
    public function __construct(CollectHelper $collectHelper)
    {
        $this->collectHelper = $collectHelper;
    }

    /**
     * @param Subject $subject
     * @param Subject $result
     * @param Subject $quote
     * @return Subject
     */
    public function afterMerge(Subject $subject, $result, Subject $quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            foreach ($subject->getAllItems() as $quoteItem) {
                if ($quoteItem->compare($item)) {
                    $quoteItem->setCollectPlaceId($item->getCollectPlaceId());
                    $quoteItem->setCollectPlaceStorageName($item->getCollectPlaceStorageName());
                    break;
                }
            }
        }

        if ($this->collectHelper->isEnableSingleStoreInCartRestriction()
            && $this->collectHelper->hasCollectItemInCart($subject->getId())
            && $this->collectHelper->hasDeliveryItemInCart($subject->getId())
        ) {
            $this->transformAllItemsToDelivery($subject);
        }
        return $result;
    }

    /**
     * @param Subject $quote
     * @return void
     */
    public function transformAllItemsToDelivery($quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item->getCollectPlaceId() && !$item->getCollectPlaceStorageName()) {
                continue;
            }
            $item->setCollectPlaceId(null);
            $item->setCollectPlaceStorageName(null);
        }
    }
}
