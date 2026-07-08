<?php

namespace Digidirect\AbstractGiftCard\Block\Form;

use Magento\Framework\App\ObjectManager;
use Digidirect\AbstractGiftCard\Model\Service\AbstractService;

/**
 * Base container block for services forms
 *
 */
class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\ServiceList
     */
    private $_serviceListHelper;

    /**
     * Container constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Digidirect\AbstractGiftCard\Helper\ServiceList $serviceListHelper
     * @param array $data
     * @param array $additionalChecks
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Digidirect\AbstractGiftCard\Helper\ServiceList $serviceListHelper,
        array $data = [],
        array $additionalChecks = []
    ) {
        parent::__construct($context, $data);
        $this->_helper = $helper;
        $this->_serviceListHelper = $serviceListHelper;
        $this->_serviceListHelper->addAdditionalChecks($additionalChecks);
    }

    /**
     * Prepare children blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        foreach ($this->_serviceListHelper->getServices() as $service) {
            $this->setChild(
                'giftcard.service.' . $service->getCode(),
                $this->_helper->getServiceFormBlock($service, $this->_layout)
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Declare template for service form block
     *
     * @param string $method
     * @param string $template
     * @return $this
     */
    public function setServiceFormTemplate($method = '', $template = '')
    {
        if (!empty($method) && !empty($template)) {
            if ($block = $this->getChildBlock('giftcard.service.' . $method)) {
                $block->setTemplate($template);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_helper->isActive() && !empty($this->_serviceListHelper->getServices())) {
            return parent::_toHtml();
        }
        return '';
    }
}
