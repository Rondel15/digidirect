<?php

namespace Digidirect\StoreLocator\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Digidirect\StoreLocator\Model\CoordinatesProviderFactory;

/**
 * Since 1.4.0 it works through coordinates provider interface. Backward incompatible changes were made
 * Constructor arguments were completely changed
 */
class StoreLocatorSaveBefore implements ObserverInterface
{
    /**
     * @var CoordinatesProviderFactory
     */
    protected $coordinatesProviderFactory;

    /**
     * StoreLocatorSaveBefore constructor.
     * @param CoordinatesProviderFactory $coordinatesProviderFactory
     */
    public function __construct(
        CoordinatesProviderFactory $coordinatesProviderFactory
    ) {
        $this->coordinatesProviderFactory = $coordinatesProviderFactory;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $model = $observer->getEvent()->getDataObject();
        if (($model->hasData('longitude') && !$model->getLongitude())
            || ($model->hasData('latitude') && !$model->getLatitude())
        ) {
            if ($coordinates = $this->getCoordinatesByAddress($model->getData())) {
                $model->setLongitude($coordinates['lng']);
                $model->setLatitude($coordinates['lat']);
            }
        }

        return $this;
    }

    /**
     * @param $address
     * @return array|bool
     */
    protected function getCoordinatesByAddress($address)
    {
        return $this->coordinatesProviderFactory->getCoordinates($address);
    }
}
