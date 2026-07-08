<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration;

/**
 * Class GenerateAccessToken is base frontend model for Generate Access Token button
 */
class GenerateAccessToken extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact
     */
    private $auth;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $configHelper;

    /**
     * GenerateAccessToken constructor.
     *
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact $auth
     * @param \Plumrocket\Newsletterpopup\Helper\Config                                   $configHelper
     * @param \Magento\Backend\Block\Template\Context                                     $context
     * @param array                                                                       $data
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact $auth,
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->auth = $auth;
        $this->configHelper = $configHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getElementHtml(
        \Magento\Framework\Data\Form\Element\AbstractElement $element
    ) {
        $disabled = false;
        $url = $this->auth->getAuthorizationCodeUrl();
        $onClick = sprintf('javascript:window.openWindowToGetConstantContactToken("%s");', $url);
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );

        if (! $this->configHelper->getConstantContactApiKey()
            || ! $this->configHelper->getConstantContactSecret()
        ) {
            $disabled = true;
        }

        $button->setData(
            [
                'style' => 'margin-top:5px',
                'label' => __('Generate Access Token'),
                'class' => 'integration-generate-token',
                'onclick' => $onClick,
                'disabled' => $disabled,
            ]
        );

        return parent::_getElementHtml($element) . $button->toHtml();
    }
}
