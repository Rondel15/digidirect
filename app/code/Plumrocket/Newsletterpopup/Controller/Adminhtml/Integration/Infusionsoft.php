<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

class Infusionsoft extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * Frontend label for integration
     *
     * InfusionSoft was renamed to Keap
     */
    const RESPONSE_FRONTEND_LABEL = 'Keap';

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\InfusionSoft $serviceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\InfusionSoft $serviceModel
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

        if (empty($this->serviceModel->getAppName())) {
            return $this->getResultError(__('Invalid App Name.'));
        }

        if ($accountInfo = $this->serviceModel->getAccountInfo()) {
            if (! empty($accountInfo['fault'])) {
                $message = ! empty($accountInfo['fault']['value']['struct'][1]['value'])
                    ? (string)$accountInfo['fault']['value']['struct'][1]['value']
                    : __('Something went wrong.');

                return $this->getResultError($message);
            } else {
                $value = ! empty($accountInfo['params']['param']['value'])
                    ? (string)$accountInfo['params']['param']['value']
                    : __('Not set yet.');
                $message = __(
                    'Success! Your Account is correct. API Passphrase "%1"',
                    $value
                );

                return $this->getResultSuccess($message, $accountInfo);
            }
        }

        return $this->getResultError();
    }
}
