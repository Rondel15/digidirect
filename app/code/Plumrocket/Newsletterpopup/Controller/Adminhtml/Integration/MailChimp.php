<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

use Plumrocket\Newsletterpopup\Model\Mcapi;

class MailChimp extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $encryptor;

    /**
     * @var Mcapi
     */
    private $mcapi;

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Helper\Config $configHelper
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Helper\Config $configHelper,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Plumrocket\Newsletterpopup\Model\Mcapi $mcapi
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->encryptor = $encryptor;
        $this->mcapi = $mcapi;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->responseFrontendLabel = 'MailChimp';
        $apiKey = $this->getRequest()->getParam('api_key');

        if (! empty($apiKey) && empty(str_replace('*', '', $apiKey))) {
            $apiKey = $this->encryptor->decrypt(
                trim($this->configHelper->getSectionConfig('integration/mailchimp/key'))
            );
        }

        if (empty($apiKey)) {
            return $this->getResultError(__('API Key must be specified.'));
        }

        /** @var \Plumrocket\Newsletterpopup\Model\Mcapi $serviceModel */
        $serviceModel = $this->mcapi->initApi($apiKey);

        if (! $serviceModel) {
            return $this->getResultError(__('Undefined API Model. Please configure before send requests.'));
        }

        $accountInfo = $serviceModel->getAccountDetails();

        if ($accountInfo && ! empty($accountInfo['account_id'])) {
            $message = sprintf(
                'Success! Your Account is correct. Account Name %s (account_id:%s, account_timezone:%s)',
                $accountInfo['account_name'],
                $accountInfo['account_id'],
                $accountInfo['account_timezone']
            );

            return $this->getResultSuccess($message, $accountInfo);
        }

        return $this->getResultError($serviceModel->errorMessage);
    }
}
