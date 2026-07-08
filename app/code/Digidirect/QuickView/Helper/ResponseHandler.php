<?php
namespace Digidirect\QuickView\Helper;

use Magento\Framework\App\RequestInterface;

/**
 * Class ResponseHandler
 * @package Digidirect\QuickView\Helper
 */
class ResponseHandler extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\View
     */
    protected $_view;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * ResponseHandler constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Json\Helper\Data $_jsonHelper
     * @param \Magento\Framework\App\Action\Context $actionContext
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\Helper\Data $_jsonHelper,
        \Magento\Framework\App\Action\Context $actionContext
    ) {
        $this->_view = $actionContext->getView();
        $this->_jsonHelper = $_jsonHelper;
        parent::__construct($context);
    }

    /**
     * @param RequestInterface $response
     * @return void
     */
    public function sendResponse($response)
    {
        /** @var \Magento\Framework\View\Result\Page $page */
        /** @var \Magento\Framework\App\ResponseInterface $response */
        $page = $this->_view->getPage();
        $page->renderResult($response);
        $response->sendResponse();
    }
}
