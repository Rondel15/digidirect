<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\AccessToken;

use Magento\Framework\App\Action\Context;
use Plumrocket\Newsletterpopup\Helper\Config;

class ConstantContact extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact
     */
    private $auth;

    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $config;

    /**
     * @param \Magento\Framework\App\Action\Context                                       $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact $auth
     * @param \Plumrocket\Newsletterpopup\Helper\Config                                   $config
     */
    public function __construct(
        Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\Authorization\ConstantContact $auth,
        Config $config
    ) {
        $this->auth = $auth;
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Generate constant contact access token action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $code = $this->getRequest()->getParam('code');

        if (! $code || ! $this->config->isModuleEnabled()) {
            return $this->_redirect('/');
        }

        $result = $this->auth->generateAccessToken($code);
        $responseBody = '
            <script type="application/javascript">
                var accessTokenMessageblock = opener.document.querySelector("fieldset#prnewsletterpopup_integration_constantcontact #row_prnewsletterpopup_integration_constantcontact_access_token .generate-token-message-block");

                if (accessTokenMessageblock) {
                    accessTokenMessageblock.parentNode.removeChild(accessTokenMessageblock);
                }

                var elem = document.createElement("div"); elem.setAttribute("class", "generate-token-message-block");';

        if (true === $result) {
            $responseBody .= 'var content = "<div id=\'result_container_constantcontact\' class=\'message message-success success\' style=\'background: none; color: green;\'><b>' . __('Access token successfully generated!') . '</b></div>";
                opener.document.querySelector("fieldset#prnewsletterpopup_integration_constantcontact input#prnewsletterpopup_integration_constantcontact_access_token").setAttribute("value", "' . $this->auth->getAccessToken() . '");';
        } else {
            $errorMessage = (isset($result['error_description'])
                ? $result['error_description']
                : __('Something went wrong.'));

            $responseBody .= 'var content = "<div id=\'result_container_constantcontact\' class=\'message message-error error\' style=\'background: none; color: red;\'><b>' . __('Connection Error!') . '</b><p>' . __($errorMessage) . '</p></div>";';
        }

        $responseBody .= "var buttonElem = opener.document.querySelector('fieldset#prnewsletterpopup_integration_constantcontact #row_prnewsletterpopup_integration_constantcontact_access_token button.integration-generate-token');
                elem.innerHTML = content;
                buttonElem.parentNode.insertBefore(elem, buttonElem.nextSibling);

                var buttonsToEnable = [
                    'prnewsletterpopup_integration_constantcontact_test_connection',
                    'prnewsletterpopup_integration_constantcontact_custom_fields',
                ];

                buttonsToEnable.forEach(function (buttonId) {
                    var btn = opener.document.getElementById(buttonId);
                    btn.classList.remove('disabled')
                    btn.disabled = false;
                });

                window.close();
            </script>";

        return $this->getResponse()->setBody($responseBody)->sendResponse();
    }
}
