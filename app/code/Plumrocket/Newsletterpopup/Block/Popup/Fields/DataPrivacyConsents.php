<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Block\Popup\Fields;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\AbstractWidget;
use Magento\Customer\Helper\Address;
use Magento\Framework\View\Element\Template\Context;
use Plumrocket\Base\Api\ExtensionStatusInterface;

class DataPrivacyConsents extends AbstractWidget
{

    /**
     * @var \Plumrocket\Base\Api\ExtensionStatusInterface
     */
    private $extensionStatus;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address                 $addressHelper
     * @param \Magento\Customer\Api\CustomerMetadataInterface  $customerMetadata
     * @param \Plumrocket\Base\Api\ExtensionStatusInterface    $extensionStatus
     * @param array                                            $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        ExtensionStatusInterface $extensionStatus,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->extensionStatus = $extensionStatus;
    }

    /**
     * Configure block if Data Privacy is enabled.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->extensionStatus->isEnabled('DataPrivacy')) {
            $this->setTemplate('Plumrocket_DataPrivacy::x-init/location_checkbox_list.phtml')
                 ->setData('locationKey', 'newsletter')
                 ->setData('scope', 'newsletter.popup.data.privacy.consents');

            // disable opening in popup to avoid conflicts between z-index
            $this->setData('denyToOpenCmsInPopup', true);

            // All popups have labels off in their own styles, so we're adding inline style to checkboxes
            $this->setData('checkboxLabelStyle', 'display: initial;');
        }

        return parent::_toHtml();
    }
}
