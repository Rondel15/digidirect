<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Rate\Edit\Button;

/**
 * Class Back
 */
class Back extends Generic
{
    /**
     * Get back button data
     * Can redirect to the corresponding method form
     *
     * @param int $sortOrder
     * @return array
     */
    public function getButtonData($sortOrder = 10)
    {
        $url = $this->getUrl('*/*/');
        if ($this->isBackToMethod() && ($this->getRate()->getMethodId() || $this->request->getParam('method_id'))) {
            $methodId = $this->getRate()->getMethodId() ?
                $this->getRate()->getMethodId() :
                $this->request->getParam('method_id');
            $url = $this->getUrl('digidirect_extendedshippingrates/extendedshippingrates_method/edit', ['id' => $methodId]);
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
