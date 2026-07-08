<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */


namespace Plumrocket\Newsletterpopup\Controller\Adminhtml;

use Magento\Framework\Controller\ResultFactory;

class Integration extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Plumrocket_Newsletterpopup::config';

    /**
     * Frontend label for integration
     */
    const RESPONSE_FRONTEND_LABEL = 'Integration';

    /**
     * @var string
     */
    public $responseFrontendLabel;

    /**
     * @var null|\Plumrocket\Newsletterpopup\Model\AbstractIntegration
     */
    public $serviceModel = null;

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (! $this->serviceModel) {
            return $this->getResultError(__('Undefined API Model'));
        }

        $this->serviceModel->setTestConnectionMode(true);
        $this->responseFrontendLabel = self::RESPONSE_FRONTEND_LABEL;
        $this->initializeRequestParams();

        return $this->getResultError();
    }

    /**
     * Initialize request params
     *
     * @return $this
     */
    private function initializeRequestParams()
    {
        $request = $this->getRequest();
        $apiUrl = $request->getParam('api_url');
        $appName = $request->getParam('app_name');
        $apiKey = $request->getParam('api_key');

        if ($apiUrl) {
            $this->serviceModel->setApiUrl($apiUrl);
        }

        $this->serviceModel->setAppName($appName);

        if ('******' !== (string)$apiKey) {
            $this->serviceModel->setApiKey($apiKey);
        }

        return $this;
    }

    /**
     * @param $data
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     */
    private function getResult($data)
    {
        /* Stop DISPATCH and POST_DISPATCH */
        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);

        return $resultJson;
    }

    /**
     * Send success response
     *
     * @param $message
     * @param null $info
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json
     */
    protected function getResultSuccess($message, $info = null)
    {
        return $this->getResult([
            'result' => 'success',
            'message' => (string)$message,
            'info' => (! empty($info) && is_array($info)) ? $info : [],
        ]);
    }

    /**
     * Send error response
     *
     * @param $message
     * @return mixed
     */
    protected function getResultError($message = null)
    {
        if (empty($message)) {
            $message = __('Resource not available. Please check configurations.');
        }

        return $this->getResult([
            'result' => 'error',
            'message' => __('%1 Response: %2', $this->responseFrontendLabel, $message),
        ]);
    }
}
