<?php
namespace Digidirect\Collect\Model;

use Digidirect\Collect\Api\DeliveryAddToCartValidationInterface;
use Digidirect\Collect\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Class DeliveryAddToCartValidation
 * @package Digidirect\Collect\Model
 */
class DeliveryAddToCartValidation implements DeliveryAddToCartValidationInterface
{
    /**
     * @var Data
     */
    protected $collectHelper;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * DeliveryAddToCartValidation constructor.
     * @param Data $collectHelper
     * @param Json $json
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $collectHelper,
        Json $json,
        LoggerInterface $logger
    ) {
        $this->json = $json;
        $this->collectHelper = $collectHelper;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function hasDeliveryItemInCart()
    {
        try {
            $result = $this->collectHelper->hasDeliveryItemInCart();
            return $this->json->serialize(['result' => $result, 'error' => false]);
        } catch (\Exception $e) {
            $this->collectHelper->logError($e->getMessage());
            return $this->json->serialize(
                [
                    'error' => true,
                    'message' => __('Something went wrong with quote delivery-validation')
                ]
            );
        }
    }
}
