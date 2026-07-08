<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2021 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Block\Popup\Fields;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;
use Magento\ReCaptchaUi\Block\ReCaptcha as UiReCaptchaBlock;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Model\Config\Source\ReCaptcha as ReCaptchaConfigSource;

/**
 * @since 4.1.3
 */
class Recaptcha extends Field
{
    public const RECAPTCHA_KEY = 'prnewsletter_popup';

    /**
     * @var string
     */
    protected $_template = 'Plumrocket_Newsletterpopup::popup/fields/recaptcha.phtml';

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $configHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Customer\Api\CustomerMetadataInterface $customerMetadata
     * @param \Magento\Customer\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Customer\Model\Options $customerOptions
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        Options $customerOptions,
        Customer $customer,
        Config $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct(
            $context,
            $addressHelper,
            $customerMetadata,
            $attributeMetadataDataProvider,
            $customerOptions,
            $customer,
            $data
        );
    }

    public function _toHtml()
    {
        if (ReCaptchaConfigSource::CUSTOM_CONFIG === $this->configHelper->getReCaptchaConfigType()) {
            return parent::_toHtml();
        }

        return $this->getDefaultRecaptchaHTML();
    }

    /**
     * Create ReCaptchaUi html (this type of ReCaptcha is supported by default for Magento 2.4.*)
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDefaultRecaptchaHTML(): string
    {
        $html = '';
        $popup = $this->getPopup();

        $data = [
            'recaptcha_for' => self::RECAPTCHA_KEY,
            'jsLayout' => [
                'components' => [
                    'recaptcha' => [
                        'component' => 'Magento_ReCaptchaFrontendUi/js/reCaptcha',
                        'reCaptchaId' => self::RECAPTCHA_KEY,
                        'settings' => [
                            'rendering' => [
                                'badge' => 'inline'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $html .= $this->getLayout()
            ->createBlock(UiReCaptchaBlock::class, self::RECAPTCHA_KEY, ['data' => $data])
            ->setNameInLayout("recaptcha-" . self::RECAPTCHA_KEY . "-" . $popup->getId())
            ->setTemplate('Magento_ReCaptchaFrontendUi::recaptcha.phtml')
            ->toHtml();

         return $html;
    }
}
