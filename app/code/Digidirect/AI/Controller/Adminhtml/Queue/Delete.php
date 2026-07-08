<?php
namespace Digidirect\AI\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;

/**
 * Class Delete
 *
 * @package Digidirect\AI\Controller\Adminhtml\Queue
 */
class Delete extends \Magento\Backend\App\Action
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
     * Delete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        QueueRepository $queueRepository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->queueRepository = $queueRepository;
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
            if (!($queue->getState() == QueueState::STATE_CLOSED
                && $queue->getStatus() == QueueStatus::STATUS_FAILED)) {
                throw new LocalizedException(__('Queue item can`t be retired with the state and status.'));
            }
            $this->queueRepository->retire($queue);
            $this->messageManager->addSuccessMessage(__('You retire the queue member.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The queue member no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Throwable $other) {
            $this->messageManager->addExceptionMessage($other, __('We can\'t delete the queue member right now.'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
