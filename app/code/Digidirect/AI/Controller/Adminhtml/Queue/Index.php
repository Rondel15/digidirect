<?php
namespace Digidirect\AI\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Digidirect\AI\Controller\Adminhtml\Queue
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * PageFactory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AI::process_queue');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Digidirect_AI::digidirect_ai_queue');
        $resultPage->addBreadcrumb(__('Digidirect'), __('Digidirect'));
        $resultPage->addBreadcrumb(__('Queue'), __('Queue'));
        $resultPage->getConfig()->getTitle()->prepend(__('Integrations Queuing'));

        return $resultPage;
    }
}
