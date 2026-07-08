<?php
namespace Digidirect\AI\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\AI\Model\Engine\Queue\QueueProcessor;
use Digidirect\AI\Model\ResourceModel\Queue\Queue\CollectionFactory as QueueCollectionFactory;
use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class MassRun
 *
 * @package Digidirect\AI\Controller\Adminhtml\Queue
 */
class MassRun extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * Queue Collection
     *
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

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
     * MassRun constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param QueueRepository $queueRepository
     * @param QueueProcessor $queueProcessor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        QueueCollectionFactory $queueCollectionFactory,
        QueueRepository $queueRepository,
        QueueProcessor $queueProcessor
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->queueRepository = $queueRepository;
        $this->queueProcessor = $queueProcessor;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $itemsRun = 0;
        $collection = $this->filter->getCollection($this->queueCollectionFactory->create());
        foreach ($collection->getItems() as $queue) {
            try {
                /* @var QueueInterface $queue*/
                if (!(($queue->getState() == QueueState::STATE_CLOSED
                        && $queue->getStatus() == QueueStatus::STATUS_FAILED)
                    || $queue->getState() == QueueState::STATE_PENDING_DEPENDS)) {
                    throw new LocalizedException(
                        __('Queue #%1 can`t be run with the state and status.', $queue->getId())
                    );
                }
                $this->queueProcessor->processQueueItem($queue);
                $itemsRun++;
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
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been run.', $itemsRun)
        );

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
