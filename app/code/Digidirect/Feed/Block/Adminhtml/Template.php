<?php

namespace Digidirect\Feed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container as GridContainer;

class Template extends GridContainer
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_template';
        $this->_blockGroup = 'Digidirect_Feed';
        $this->_headerText = __('Manage Feed Templates');
        $this->_addButtonLabel = __('Add Template');

        $this->buttonList->add('import', [
            'label' => __('Import Templates'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/import') . '\')',
            'class' => 'import',
        ]);

        parent::_construct();
    }
}
