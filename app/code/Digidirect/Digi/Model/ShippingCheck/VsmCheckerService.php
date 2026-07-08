<?php

namespace Digidirect\Digi\Model\ShippingCheck;

use Digidirect\MyStoreWidget\Helper\Data as WidgetStoreHelper;
use Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterfaceFactory;
use Digidirect\ShippingAvailabilityCheck\Model\CheckManagement;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\EstimateAddressInterfaceFactory;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class VsmCheckerService
{
    const VSM_PREFIX = 'vsm';

    /**
     * @var WidgetStoreHelper
     */
    private $widgetStoreHelper;

    /**
     * @var CheckManagement
     */
    private $shipCheckManager;

    /**
     * @var EstimateAddressInterfaceFactory
     */
    private $estimateAddressFactory;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * VsmCheckerService constructor.
     * @param CheckManagement $shipCheckManager
     * @param CheckoutSession $checkoutSession
     * @param EstimateAddressInterfaceFactory $estimateAddressFactory
     */
    public function __construct(
        CheckManagement $shipCheckManager,
        CheckoutSession $checkoutSession,
        EstimateAddressInterfaceFactory $estimateAddressFactory

    ) {
        $this->shipCheckManager = $shipCheckManager;
        $this->estimateAddressFactory = $estimateAddressFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param null|EstimateAddressInterface $addressData
     * @return mixed|null
     */
    public function getCheckResult(?EstimateAddressInterface $addressData)
    {
        if ($addressData !== null) {
            $currentQuote = $this->checkoutSession->getQuote();
            if ($currentQuote !== null) {
                try {
                    $result = $this->shipCheckManager->estimateByAddress($currentQuote, $addressData);
                } catch (LocalizedException $e) {
                    $result = [];
                }
                $machResult = array_reduce($result, static function ($acc, $ship) {
                    $data = $ship->getData();
                    if (isset($data['method_code'])
                        && !empty($data['method_code'])
                        && \is_string($data['method_code'])
                    ) {
                        $methodCode = strtolower(trim($data['method_code']));
                        $acc[$data['method_code']] = mb_strpos($methodCode, self::VSM_PREFIX) === 0;
                    }
                    return $acc;
                }, []);
                $machResult = array_filter($machResult);
                return count($machResult) > 0
                    ? [$addressData->getPostcode() => true]
                    : [$addressData->getPostcode() => false];
            }
        }
        return ['error' => true];
    }

    /**
     * @param string $country
     * @param string $postcode
     * @return null|EstimateAddressInterface
     */
    public function createAddressData(string $country, string $postcode)
    {
        $addressData = null;
        if (!empty($country) && !empty($postcode) && preg_match('/^[a-z0-9 .\-]+$/i', $postcode)) {
            $addressData = $this->estimateAddressFactory->create();
            $addressData->setCountryId($country);
            $addressData->setPostcode($postcode);
        }
        return $addressData;
    }
}
