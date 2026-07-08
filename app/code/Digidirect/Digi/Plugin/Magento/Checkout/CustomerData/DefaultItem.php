<?php

namespace Digidirect\Digi\Plugin\Magento\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;
use Digidirect\Digi\Model\ShippingCheck\SddCheckerService;

class DefaultItem
{
    /**
     * @var SddCheckerService
     */
    private $sddCheckerService;

    /**
     * DefaultItem constructor.
     * @param SddCheckerService $sddCheckerService
     */
    public function __construct(SddCheckerService $sddCheckerService)
    {
        $this->sddCheckerService = $sddCheckerService;
    }

    /**
     * @param \Magento\Checkout\CustomerData\DefaultItem $subject
     * @param array $result
     * @param Item $item
     * @return array
     */
    public function afterGetItemData(
        \Magento\Checkout\CustomerData\DefaultItem $subject,
        array $result,
        Item $item
    ) {
        $availabilitySddByProduct = false;
        $product = $item->getProduct();
        $productData = $this->sddCheckerService->createProductData($product->getId(), $item->getQty());
        $checkedData = $this->sddCheckerService->getCheckResult($productData);
        if (!empty($checkedData) && isset($checkedData[$product->getId()])) {
            $availabilitySddByProduct = $checkedData[$product->getId()];
        }
        $result['is_sdd_available'] = $availabilitySddByProduct;
        return $result;
    }
}
