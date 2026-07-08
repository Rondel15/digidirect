<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed\Edit\Tab\General;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;

class Info extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Info constructor.
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
        $this->setTemplate('feed/edit/tab/general/info.phtml');
    }

    /**
     * Current feed model
     *
     * @return \Digidirect\Feed\Model\Feed
     */
    public function getFeed()
    {
        return $this->registry->registry('current_model');
    }
}
