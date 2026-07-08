<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form;

use Magento\Store\Model\ScopeInterface;

class RedirectUri extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    private $configHelper;

    /**
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array                                   $data
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configHelper = $configHelper;
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return '<input id="'. $element->getHtmlId() .'" type="text" name="" value="' . $this->configHelper->getConstantContactRedirectUri() . '" class="input-text" style="background-color: #EEE; color: #999;" readonly="readonly" />';
    }
}
