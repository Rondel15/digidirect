<?php
namespace Digidirect\Blog\Controller\Adminhtml\Comment;

use Digidirect\Blog\Controller\Adminhtml\AbstractMassAction;
use Digidirect\Blog\Model\ResourceModel\Comment\CollectionFactory;
use Digidirect\Blog\Api\CommentRepositoryInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassStatus
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @var CommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * MassStatus constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CommentRepositoryInterface $commentRepository
    ) {
        parent::__construct($context, $filter);
        $this->commentRepository = $commentRepository;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action to collection items
     *
     * @param AbstractCollection $collection
     * @return ResponseInterface|ResultInterface
     */
    protected function massAction(AbstractCollection $collection)
    {
        $status = $this->getRequest()->getParam('status');
        $recordsUpdated = $this->commentRepository->updateStatus($collection->getAllIds(), (int)$status);
        if ($recordsUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $recordsUpdated));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());
        $this->cacheInvalidator->invalidate();
        return $resultRedirect;
    }
}
