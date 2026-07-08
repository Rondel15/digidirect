<?php
namespace Digidirect\AI\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Engine\Queue\QueueProcessor;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;

/**
 * Class Run
 *
 * @package Digidirect\AI\Controller\Adminhtml\Queue
 */
class Run extends \Magento\Backend\App\Action
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
     * Run constructor.
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
            /* @var QueueInterface $queue*/
            $queue = $this->queueRepository->get($queueId);
            if (!(($queue->getState() == QueueState::STATE_CLOSED
                && $queue->getStatus() == QueueStatus::STATUS_FAILED)
                || $queue->getState() == QueueState::STATE_PENDING_DEPENDS)) {
                throw new LocalizedException(__('Queue item can`t be run with the state and status.'));
            }
            $this->queueProcessor->processQueueItem($queue);
            $this->messageManager->addSuccessMessage(__('Done. Please see details.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The queue member no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Throwable $other) {
            $this->messageManager->addExceptionMessage(
                $other,
                __('We can\'t run the queue member right now. Please see details.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
