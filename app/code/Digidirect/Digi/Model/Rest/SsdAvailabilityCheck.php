<?php
namespace Digidirect\Digi\Model\Rest;

use Digidirect\Digi\Api\Rest\CheckSsdAvailabilityInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Digidirect\Digi\Model\ShippingCheck\SddCheckerService;

/**
 * Class AbstractEntityRest
 * @package Digidirect\Digi\Model\Rest
 */
class SsdAvailabilityCheck implements CheckSsdAvailabilityInterface
{
    /** @var Json  */
    private $jsonHelper;
    /**
     * @var SddCheckerService
     */
    private $sddCheckerService;

    /**
     * SsdAvailabilityCheck constructor.
     * @param SddCheckerService $sddCheckerService
     * @param Json $jsonHelper
     */
    public function __construct(
        SddCheckerService $sddCheckerService,
        Json $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->sddCheckerService = $sddCheckerService;
    }

    /**
     * Retrieve AbstractEntity
     * @param string $id
     * @return string
     */
    public function getById($id)
    {
        $productData = $this->sddCheckerService->createProductData($id);
        try {
            $result = $this->sddCheckerService->getCheckResult($productData);
        } catch (\Throwable $e) {
            return $this->jsonHelper->serialize(['error' => $this->getErrorMessage()]);
        }
        return $this->jsonHelper->serialize([
            'sddCheckResult' => $result
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
