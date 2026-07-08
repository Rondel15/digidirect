<?php

namespace Digidirect\Feed\Block\Adminhtml\Rule;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use \Digidirect\Feed\Block\Adminhtml\AbstractEdit as EditContainer;

class Edit extends EditContainer
{
    /**
     * @var string
     */
    protected $_objectId = 'rule_id';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_rule';

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

        if ($this->getRequest()->getParam('popup')) {
            $this->buttonList->remove('back');
            $this->buttonList->add('close', [
                'label' => __('Close Window'),
                'class' => 'cancel',
                'onclick' => 'window.close()',
                'level' => -1,
            ]);
        } else {
            $this->_replaceSaveButtonWithSaveSplitButton();
        }

        $this->buttonList->update('save', 'label', __('Save Filter'));
    }
}
