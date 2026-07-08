<?php

namespace Digidirect\Feed\Block\Adminhtml\Template;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

use \Digidirect\Feed\Block\Adminhtml\AbstractEdit as EditContainer;

class Edit extends EditContainer
{
    /**
     * @var string
     */
    protected $_objectId = 'template_id';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_template';

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_replaceSaveButtonWithSaveSplitButton();

        if ($this->registry->registry('current_model')->getId()) {
            $exportUrl = $this->getUrl('*/*/export', ['id' => $this->registry->registry('current_model')->getId()]);
            $this->buttonList->add('Export', [
                'label' => __('Export'),
                'class' => 'export',
                'onclick' => 'setLocation(\'' . $exportUrl . '\')'
            ], -110);
        }
    }
}
