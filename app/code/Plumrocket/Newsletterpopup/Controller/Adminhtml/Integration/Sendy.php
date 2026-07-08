<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration;

/**
 * Class Sendy
 *
 * @package Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
 */
class Sendy extends \Plumrocket\Newsletterpopup\Controller\Adminhtml\Integration
{
    /**
     * Frontend label for integration
     */
    const RESPONSE_FRONTEND_LABEL = 'Sendy';

    /**
     * Test constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Sendy $serviceModel
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Plumrocket\Newsletterpopup\Model\Integration\Sendy $serviceModel
    ) {
        $this->serviceModel = $serviceModel;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|
     * \Magento\Framework\Controller\Result\Json|
     * \Magento\Framework\Controller\ResultInterface|mixed
     */
    public function execute()
    {
        parent::execute();

        $appKey = htmlspecialchars($this->getRequest()->getParam('api_key'));
        $appUrl = htmlspecialchars($this->getRequest()->getParam('api_url'));

        $this->responseFrontendLabel = self::RESPONSE_FRONTEND_LABEL;
        $response = $this->serviceModel->testConnection($appKey, $appUrl);

        if (isset($response['success']) && true === $response['success']) {
            $message = __('Success! Your Account is correct.');

            return $this->getResultSuccess($message);
        }

        $message = isset($response['error_message']) ? $response['error_message'] : __('Something went wrong.');

        return $this->getResultError($message);
    }
}
