<?php
namespace Digidirect\ShippingAvailabilityCheckCollect\Plugin\Collect\Block;

use Digidirect\ShippingAvailabilityCheck\Helper\Data;

/**
 * Class CollectPlaces
 * @package Digidirect\ShippingAvailabilityCheckCollect\Plugin\Collect\Block
 */
class CollectPlaces
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * CollectPlaces constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param \Digidirect\Collect\Block\CollectPlaces $subject
     * @param int $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIsPostcodeFieldNeeded(
        \Digidirect\Collect\Block\CollectPlaces $subject,
        $result
    ) {
        return $this->helper->isShippingAvailabilityCheckEnable() ? $result : true;
    }
}
