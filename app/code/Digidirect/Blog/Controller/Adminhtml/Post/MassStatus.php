<?php

namespace Digidirect\Blog\Controller\Adminhtml\Post;

use Digidirect\Blog\Controller\Adminhtml\AbstractMassAction;
use Digidirect\Blog\Model\ResourceModel\Post\CollectionFactory;
use Digidirect\Blog\Api\PostRepositoryInterface;
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
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * MassStatus constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PostRepositoryInterface $postRepository
    ) {
        parent::__construct($context, $filter);
        $this->postRepository = $postRepository;
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
        $recordsUpdated = $this->postRepository->updateStatus($collection->getAllIds(), (int)$status);
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
