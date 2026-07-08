<?php
namespace Digidirect\AI\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Engine\Queue\QueueProcessor;

/**
 * Class Details
 *
 * @package Digidirect\AI\Controller\Adminhtml\Queue
 */
class Details extends \Magento\Backend\App\Action
{
    /**
     * PageFactory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Queue Factory
     *
     * @var QueueRepository
     */
    protected $queueRepository;

    /**
     * Queue Processor
     *
     * @var QueueProcessor
     */
    protected $queueProcessor;

    /**
     * Details constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param QueueRepository $queueRepository
     * @param QueueProcessor $queueProcessor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QueueRepository $queueRepository,
        QueueProcessor $queueProcessor
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->queueRepository = $queueRepository;
        $this->queueProcessor = $queueProcessor;
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
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $queueId = intval($this->getRequest()->getParam('queue_id'));
        try {
            $queue = $this->queueRepository->get($queueId);
            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu('Digidirect_AI::digidirect_ai_queue');
            $resultPage->addBreadcrumb(__('Digidirect'), __('Digidirect'));
            $resultPage->addBreadcrumb(__('Queue'), __('Queue'));
            $resultPage->addBreadcrumb($queue->getInitiator(), $queue->getInitiator());
            $resultPage->getConfig()->getTitle()->prepend(
                __('View Queue: %1(%2)', $queue->getInitiator(), $queue->getProcessCode())
            );

            return $resultPage;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The queue member no longer exists.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        } catch (\Throwable $other) {
            $this->messageManager->addExceptionMessage($other, __('We can\'t open the queue member right now.'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }
    }
}
