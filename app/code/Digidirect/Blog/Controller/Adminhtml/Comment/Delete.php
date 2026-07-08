<?php

namespace Digidirect\Blog\Controller\Adminhtml\Comment;

use Digidirect\Blog\Model\CacheInvalidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Digidirect\Blog\Api\CommentRepositoryInterface;
use Magento\Store\Model\Store;


class Delete extends Action
{
    /**
     * @var CommentRepositoryInterface
     */
    protected $repository;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * Delete constructor.
     * @param Context $context
     * @param CommentRepositoryInterface $repository
     * @param CacheInvalidator $cacheInvalidator
     */
    public function __construct(
        Action\Context $context,
        CommentRepositoryInterface $repository,
        CacheInvalidator $cacheInvalidator
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->cacheInvalidator = $cacheInvalidator;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->repository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the comment item.'));
                $this->cacheInvalidator->invalidate();
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a comment item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check if Is allowed to delete
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogcomment');
    }
}
