<?php
namespace Digidirect\AbstractAttributes\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 * @package Magento\Customer\Block\Adminhtml\Edit
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        return [
            'label'      => __('Back'),
            'on_click'   => sprintf('location.href = \'%s\';', $this->getBackUrl()),
            'class'      => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     * @return string
     */
    public function getBackUrl()
    {
        if ($attrId = $this->context->getRequest()->getParam('attribute_id')) {
            return $this->getUrl('catalog/product_attribute/edit', [
                'attribute_id' => $attrId,
                'active_tab'   => 'advanced_options_properties'
            ]);
        }
        return $this->getUrl('*/*/');
    }
}
