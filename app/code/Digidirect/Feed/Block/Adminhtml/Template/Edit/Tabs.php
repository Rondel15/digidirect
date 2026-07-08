<?php

namespace Digidirect\Feed\Block\Adminhtml\Template\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Template Information'));
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->addTab('general_section', [
            'label' => __('Template Information'),
            'title' => __('Template Information'),
            'content' => $this->getLayout()->createBlock('\Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\General')
                ->toHtml(),
        ]);

        $this->addTab('csv_section', [
            'label' => __('Content Settings'),
            'title' => __('Content Settings'),
            'content' => $this->getLayout()->createBlock('\Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Csv')
                ->toHtml(),
        ]);

        $this->addTab('xml_section', [
            'label' => __('Content Settings'),
            'title' => __('Content Settings'),
            'content' => $this->getLayout()->createBlock('\Digidirect\Feed\Block\Adminhtml\Template\Edit\Tab\Schema\Xml')
                ->toHtml(),
        ]);

        return parent::_beforeToHtml();
    }
}
