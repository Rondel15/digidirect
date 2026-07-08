<?php
namespace Digidirect\InfiniteScroll\Model;

use Digidirect\InfiniteScroll\Helper\Data as InfiniteScrollHelper;
use Digidirect\InfiniteScroll\Model\Config\Data as InfiniteScrollConfig;
use Magento\Framework\App\Action\Context;

class Handler
{
    use ProcessorTrait;

    const HANDLE = 'infinitescroll';

    /**
     * @var array
     */
    protected $_requiredFields = ['url', 'content', 'totalCount', 'currentCount', 'perPageCount'];

    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var InfiniteScrollHelper
     */
    protected $_helper;
    
    protected $logger;

    /**
     * Handler constructor.
     * @param Context $context
     * @param InfiniteScrollConfig $config
     * @param ProcessorFactory $factory
     * @param InfiniteScrollHelper $helper
     */
    public function __construct(
        Context $context,
        InfiniteScrollConfig $config,
        ProcessorFactory $factory,
        InfiniteScrollHelper $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_context = $context;
        $this->_infiniteScrollConfig = $config;
        $this->_factory = $factory;
        $this->_helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Handle Infinite scroll request
     * 
     * @param string $action
     * @return String
     */
    public function handleRequest($action)
    {
        /** @var ProcessorInterface $processor */
        $processor = $this->_getProcessor($action);
        if ($this->_helper->isEnabled() && $processor && $processor->isEnabled()
            && $this->_context->getRequest()->getParam(InfiniteScrollHelper::PARAM_NAME)
        ) {
            $html = $processor->process();
            if ($html) {
                $this->_validateResponse($html);
                $this->_sendResponse(json_encode($html));
                //$this->logger->info('handleRequest HTML: '. json_encode($html));
            }
        }

        return false;
    }

    /**
     * Infinite Scroll module initialization in layout
     *
     * @param string $handle
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return bool
     */
    public function init($handle, \Magento\Framework\View\LayoutInterface $layout)
    {
        $processor = $this->_getProcessor($handle);
        if ($this->_helper->isEnabled() && $processor && $processor->isEnabled()) {
            $layout->getUpdate()->addHandle(self::HANDLE);
            $layout->getUpdate()->addHandle(self::HANDLE . '_' . $handle);
        }
        return true;
    }

    /**
     * Send Infinite Scroll html response
     *
     * @param string $html
     * @return void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    protected function _sendResponse($html)
    {
        $this->_context->getResponse()->setBody($html)->sendResponse();
        /** @todo eliminate usage of exit statement */
        exit;
    }

    /**
     * @param array $data
     * @throws \Exception
     * @return void
     */
    protected function _validateResponse($data)
    {
        foreach ($this->_requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \Exception(__('Response is not valid, required field %1 is missed', $field));
            }
        }
    }
}
