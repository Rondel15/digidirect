<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration\ConstantContact;

/**
 * Class TestConnection is base frontend model for Test Connection button
 */
class TestConnection extends \Plumrocket\Newsletterpopup\Block\Adminhtml\System\Config\Form\Integration\TestConnection
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function isDisabled()
    {
        return ! $this->configHelper->getConstantContactApiKey()
            || ! $this->configHelper->getConstantContactSecret()
            || ! $this->configHelper->getConstantContactAccessToken();
    }
}
