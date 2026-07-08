<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Model\Popup;

use Magento\Framework\UrlInterface;
use Plumrocket\Newsletterpopup\Helper\Config;
use Plumrocket\Newsletterpopup\Model\Config\Source\Redirectto;

/**
 * Retrieve the page to which the user will be redirected after subscribe
 *
 * @since 4.0.0
 */
class GetSuccessPage
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param \Plumrocket\Newsletterpopup\Helper\Config $config
     * @param \Magento\Framework\UrlInterface           $urlBuilder
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param \Plumrocket\Newsletterpopup\Api\Data\PopupInterface $popup
     * @return string
     */
    public function execute($popup): string
    {
        if ($this->config->isModuleEnabled()) {
            switch ($popup->getSuccessPage()) {
                case '':
                case Redirectto::STAY_ON_PAGE:
                    return '';
                case Redirectto::CUSTOM_URL:
                    return $popup->getCustomSuccessPage();
                case Redirectto::ACCOUNT_PAGE:
                    return $this->urlBuilder->getUrl('customer/account');
                case Redirectto::LOGIN_PAGE:
                    return $this->urlBuilder->getUrl('customer/account/login');
                default:
                    return $this->urlBuilder->getUrl($popup->getSuccessPage());
            }
        }

        return '';
    }
}
