<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

/**
 * Class CampaignMonitor
 *
 * @package Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
 */
class CampaignMonitor extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * Frontend label for integration
     */
    const RESPONSE_FRONTEND_LABEL = 'CampaignMonitor';

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\CampaignMonitor $serviceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\CampaignMonitor $serviceModel
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

        $this->responseFrontendLabel = self::RESPONSE_FRONTEND_LABEL;
        $accountInfo = $this->serviceModel->getAccountInfo();

        if ($accountInfo && isset($accountInfo[0]['ClientID'])) {
            return $this->getResultSuccess(__(
                'Success! Your Account is correct. Account ID "%1"',
                $accountInfo[0]['ClientID']
            ), $accountInfo);
        }

        if ($accountInfo && isset($accountInfo['message'])) {
            return $this->getResultError($accountInfo['message']);
        }

        return $this->getResultError();
    }
}
