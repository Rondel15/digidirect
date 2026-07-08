<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

class IContact extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * Frontend label for integration
     */
    const RESPONSE_FRONTEND_LABEL = 'Icontact';

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\IContact $serviceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\IContact $serviceModel
    ) {
        $this->serviceModel = $serviceModel;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        parent::execute();

        $tempSecretId = htmlspecialchars($this->getRequest()->getParam('api_temp_secret'));
        $accountId = htmlspecialchars($this->getRequest()->getParam('api_account_id'));
        $userId = htmlspecialchars($this->getRequest()->getParam('api_user_id'));

        $this->responseFrontendLabel = self::RESPONSE_FRONTEND_LABEL;
        $accountInfo = $this->serviceModel->getAccountInfo($tempSecretId, $accountId, $userId);

        if ($accountInfo && isset($accountInfo['account']['email'])) {
            return $this->getResultSuccess(__(
                'Success! Your Account is correct. Account ID "%1"',
                $accountInfo['account']['email']
            ), $accountInfo);
        }

        if ($accountInfo && isset($accountInfo['message'])) {
            return $this->getResultError($accountInfo['message']);
        }

        return $this->getResultError();
    }
}
