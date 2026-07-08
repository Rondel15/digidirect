<?php
namespace Digidirect\Digi\Model\Rest;

use Digidirect\Digi\Api\Rest\CheckVsmAvailabilityInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Digidirect\Digi\Model\ShippingCheck\VsmCheckerService;

/**
 * Class AbstractEntityRest
 * @package Digidirect\Digi\Model\Rest
 */
class VsmAvailabilityCheck implements CheckVsmAvailabilityInterface
{
    /** @var Json  */
    private $jsonHelper;
    /**
     * @var VsmCheckerService
     */
    private $vsmCheckerService;

    /**
     * SsdAvailabilityCheck constructor.
     * @param VsmCheckerService $vsmCheckerService
     * @param Json $jsonHelper
     */
    public function __construct(
        VsmCheckerService $vsmCheckerService,
        Json $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->vsmCheckerService = $vsmCheckerService;
    }

    /**
     * @param string $country
     * @param string $postCode
     * @return bool|false|mixed|string
     */
    public function getByCountryAndPostCode($country, $postCode)
    {
        $addressData = $this->vsmCheckerService->createAddressData($country, $postCode);
        try {
            $result = $this->vsmCheckerService->getCheckResult($addressData);
        } catch (\Throwable $e) {
            return $this->jsonHelper->serialize(['error' => $this->getErrorMessage()]);
        }
        return $this->jsonHelper->serialize([
            'vsmCheckResult' => $result
        ]);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getErrorMessage()
    {
        return __('Something went wrong');
    }
}
