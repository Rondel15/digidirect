<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

class Hubspot extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * Frontend label for integration
     */
    const RESPONSE_FRONTEND_LABEL = 'HubSpot';

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\HubSpot $serviceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\HubSpot $serviceModel
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
            if (! empty($accountInfo['portalId'])) {
                $message = __(
                    'Success! Your Account is correct. Portal %1 (%2 %3)',
                    $accountInfo['portalId'],
                    $accountInfo['timeZone'],
                    $accountInfo['currency']
                );

                return $this->getResultSuccess($message, $accountInfo);
            }

            if (! empty($accountInfo['status'])
                && 'error' == $accountInfo['status']
            ) {
                $message = ! empty($accountInfo['message'])
                    ? (string)$accountInfo['message']
                    : null;

                return $this->getResultError($message);
            }
        }

        return $this->getResultError();
    }
}
