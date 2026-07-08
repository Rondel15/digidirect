<?php
namespace Digidirect\AbstractEntity\Ui\Component\Listing\Button;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class AddButton
 */
class AddButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        if ($set = $this->getAttributeSet()) {
            $label = __('Add %1', $set->getAttributeSetName());
        } else {
            $label = __('Add new Record');
        }

        return [
            'label' => $label,
            'on_click' => sprintf("location.href = '%s';", $this->getAddUrl()),
            'class' => 'primary',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for Add button
     *
     * @return string
     */
    public function getAddUrl()
    {
        $params = [];
        if ($set = $this->getAttributeSet()) {
            $params = [AbstractEntityInterface::ATTRIBUTE_SET_ID => $set->getId()];
        }
        return $this->getUrl('*/*/new', $params);
    }
}
