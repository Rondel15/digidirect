<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Quote\Edit\Button;

class Back extends Generic
{
    /**
     * @param int $sortOrder
     * @return array
     */
    public function getButtonData($sortOrder = 10)
    {
        $label = __('Back');
        $onClick = sprintf("location.href = '%s';", $this->context->getUrl('*/*/'));
        return [
            'label' => $label,
            'on_click' => $onClick,
            'class' => 'back',
            'sort_order' => $sortOrder
        ];
    }
}
