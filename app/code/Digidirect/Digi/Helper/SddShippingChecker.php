<?php
declare(strict_types=1);

namespace Digidirect\Digi\Helper;

use Digidirect\Digi\Model\ShippingCheck\SddCheckerService;

/**
 * Class SddShippingChecker
 * @package Digidirect\Digi\Helper
 */
class SddShippingChecker extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var SddCheckerService
     */
    private $sddCheckerService;

    /**
     * SddShippingChecker constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SddCheckerService $sddCheckerService
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SddCheckerService $sddCheckerService

    ) {
        parent::__construct($context);

        $this->sddCheckerService = $sddCheckerService;
    }

    /**
     * @param string | int $productId
     * @param string| int $qty
     * @return \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface
     */
    public function createProductData($productId, $qty = 1)
    {
        return $this->sddCheckerService->createProductData($productId, $qty);
    }

    /**
     * @param \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface|null $productData
     * @return bool
     */
    public function getCheckResult($productData)
    {
        $result = $this->sddCheckerService->getCheckResult($productData);
        if (!empty($result) && isset($result[$productData->getProductId()])) {
            return $result[$productData->getProductId()];
        }
        return false;
    }
}
