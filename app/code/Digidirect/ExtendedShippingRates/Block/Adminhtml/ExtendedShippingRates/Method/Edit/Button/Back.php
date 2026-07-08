<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Method\Edit\Button;

/**
 * Class Back
 */
class Back extends Generic
{
    /**
     * Get back button data
     * Can redirect to the corresponding carrier form
     *
     * @param int $sortOrder
     * @return array
     */
    public function getButtonData($sortOrder = 10)
    {
        $url = $this->getUrl('*/*/');
        if ($this->isBackToCarrier() && $this->getMethod()->getCarrierId()) {
            $carrierId = $this->getMethod()->getCarrierId();
            $url = $this->getUrl(
                'digidirect_extendedshippingrates/extendedshippingrates_carrier/edit',
                ['id' => $carrierId]
            );
        }

        $label = __('Back');
        $onClick = sprintf("location.href = '%s';", $url);
        $result = [
            'label' => $label,
            'on_click' => $onClick,
            'class' => 'back',
            'sort_order' => $sortOrder
        ];

        return $result;
    }
}
