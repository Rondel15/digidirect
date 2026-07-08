<?php
namespace Digidirect\CollectAbstractEntity\Observer\Collect;

use Digidirect\CollectAbstractEntity\Api\Data\CollectFields\Constants;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class FormatCollectPlacesResult
 * @package Digidirect\CollectAbstractEntity\Observer\Collect
 */
class FormatCollectPlacesResult implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var DataObject $transport */
        $transport = $observer->getEvent()->getData('transportObject');
        $data = $transport->getData('data');
        if (empty($data)) {
            return;
        }

        foreach ($data as $key => $datum) {
            $item = empty($datum['collectplace']) ? false : $datum['collectplace'];
            if (!$item instanceof \Digidirect\CollectAbstractEntity\Api\Data\CollectPlaceInterface) {
                continue;
            }
            $data[$key]['collectplace'] = [
                'id' => $item->getId(),
                Constants::COLLECT_FIELD_NAME => $item->getName(),
                Constants::COLLECT_FIELD_ADDRESS => $item->getAddress(),
                Constants::COLLECT_FIELD_LATITUDE => $item->getLatitude(),
                Constants::COLLECT_FIELD_LONGITUDE => $item->getLongitude(),
                Constants::COLLECT_FIELD_POSTCODE => $item->getPostcode(),
            ];
        }
        $transport->setData('data', $data);
        return;
    }
}
