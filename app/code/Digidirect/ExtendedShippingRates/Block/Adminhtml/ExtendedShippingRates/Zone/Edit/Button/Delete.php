<?php
namespace Digidirect\ExtendedShippingRates\Block\Adminhtml\ExtendedShippingRates\Zone\Edit\Button;

class Delete extends Generic
{

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getZone()->getId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->context->getUrl('*/*/delete', ['id' => $this->getZone()->getId()]);
    }
}
