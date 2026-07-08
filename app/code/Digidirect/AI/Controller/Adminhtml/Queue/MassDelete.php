<?php
namespace Digidirect\AI\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Digidirect\AI\Model\ResourceModel\Queue\Queue\CollectionFactory as QueueCollectionFactory;
use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;
use Digidirect\AI\Model\Engine\Queue\Status as QueueStatus;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class MassDelete
 *
 * @package Digidirect\AI\Controller\Adminhtml\Queue
 */
class MassDelete extends \Magento\Backend\App\Action
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
     * MassDelete constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter,
        QueueCollectionFactory $queueCollectionFactory,
        QueueRepository $queueRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->queueRepository = $queueRepository;
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $itemsRetired = 0;
        $collection = $this->filter->getCollection($this->queueCollectionFactory->create());
        foreach ($collection->getItems() as $queue) {
            try {
                /* @var QueueInterface $queue*/
                if (!($queue->getState() == QueueState::STATE_CLOSED
                    && $queue->getStatus() == QueueStatus::STATUS_FAILED)) {
                    throw new LocalizedException(
                        __('Queue item #%1 can`t be retired with the state and status.', $queue->getId())
                    );
                }
                $this->queueRepository->retire($queue);
                $itemsRetired++;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The queue member no longer exists.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Throwable $other) {
                $this->messageManager->addExceptionMessage($other, __('We can\'t delete the queue member right now.'));
            }
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been retired.', $itemsRetired)
        );

        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
}
