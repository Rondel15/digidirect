<?php

namespace Digidirect\Blog\Controller\Adminhtml\Category;

use Digidirect\Blog\Model\CacheInvalidator;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Digidirect\Blog\Api\CategoryRepositoryInterface;

class Delete extends Action
{
    /**
     * @var CategoryRepositoryInterface
     */
    protected $repository;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * Delete constructor.
     * @param Context $context
     * @param CategoryRepositoryInterface $repository
     * @param CacheInvalidator $cacheInvalidator
     */
    public function __construct(
        Action\Context $context,
        CategoryRepositoryInterface $repository,
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
                $this->messageManager->addSuccessMessage(__('You deleted the category item.'));
                $this->cacheInvalidator->invalidate();
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a category item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check if Is allowed to delete
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogcat');
    }
}
