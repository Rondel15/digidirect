<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

class Activecampaign extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * Frontend label for integration
     */
    const RESPONSE_FRONTEND_LABEL = 'ActiveCampaign';

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Activecampaign $serviceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\Activecampaign $serviceModel
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

        if ($accountInfo) {
            if ($accountInfo['result_code'] == 1) {
                $message = __(
                    'Success! Your Account is correct. %1 (%2)',
                    $accountInfo['account'],
                    $accountInfo['email']
                );

                return $this->getResultSuccess($message, $accountInfo);
            } else {
                $message = ! empty($accountInfo['result_message'])
                    ? (string)$accountInfo['result_message']
                    : null;

                return $this->getResultError($message);
            }
        }

        return $this->getResultError();
    }
}
