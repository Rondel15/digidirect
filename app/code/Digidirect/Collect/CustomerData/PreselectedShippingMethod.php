<?php
namespace Digidirect\Collect\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Digidirect\Collect\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Class PreselectedShippingMethod
 * @package Digidirect\Collect\CustomerData
 */
class PreselectedShippingMethod implements SectionSourceInterface
{
    const PRESELECTED_SHIPPING_METHOD_KEY = 'preselected_shipping_method';
    const COLLECT_PRESELECTED = 'collect';
    const DELIVERY_PRESELECTED = 'delivery';

    /**
     * @var Data
     */
    protected $collectHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DeliveryAddToCartValidation constructor.
     * @param Data $collectHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $collectHelper,
        LoggerInterface $logger
    ) {
        $this->collectHelper = $collectHelper;
        $this->logger = $logger;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        try {
            $isCollect = $this->collectHelper->hasCollectItemInCart();
            $isDelivery = $this->collectHelper->hasDeliveryItemInCart();
            $result = [
                self::PRESELECTED_SHIPPING_METHOD_KEY =>
                    $isCollect ? self::COLLECT_PRESELECTED : ($isDelivery ? self::DELIVERY_PRESELECTED : null),
                'error' => false
            ];
        } catch (\Exception $e) {
            $this->collectHelper->logError($e->getMessage());
            $result = [
                'error' => true,
                'error_message' => __('Something went wrong with preselected shipping method checking')
            ];
        }
        return $result;
    }
}
