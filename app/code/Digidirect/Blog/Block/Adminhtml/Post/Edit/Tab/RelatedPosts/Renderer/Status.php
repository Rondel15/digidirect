<?php
namespace Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedPosts\Renderer;

/**
 * Class Status
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $row->getStatus() ? __('Enabled') : __('Disabled');
    }
}
