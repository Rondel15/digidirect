<?php

namespace Digidirect\Blog\Controller\Adminhtml\Comment;

use Digidirect\Blog\Controller\Adminhtml\AbstractSaveAction;
use Digidirect\Blog\Model\CacheInvalidator;
use Magento\Backend\App\Action;
use Digidirect\Blog\Api\CommentRepositoryInterface;
use Digidirect\Blog\Model\CommentFactory;
use Digidirect\Blog\Model\CommentRepository;
use Magento\Framework\Exception\LocalizedException;

class Save extends AbstractSaveAction
{
    /**
     * @var CommentRepository
     */
    protected $commentRepository;

    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var CacheInvalidator
     */
    protected $cacheInvalidator;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param CommentRepositoryInterface $commentRepository
     * @param CommentFactory $commentFactory
     * @param CacheInvalidator $cacheInvalidator
     */
    public function __construct(
        Action\Context $context,
        CommentRepositoryInterface $commentRepository,
        CommentFactory $commentFactory,
        CacheInvalidator $cacheInvalidator
    ) {
        $this->commentRepository = $commentRepository;
        $this->commentFactory = $commentFactory;
        $this->cacheInvalidator = $cacheInvalidator;
        parent::__construct($context);
    }

    /**
     * Save Menu Item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getId('comment');
        $postData = $this->getRequest()->getPostValue();
        if ($postData) {
            try {
                $model = $this->commentFactory->create();
                if (!empty($id)) {
                    $model = $this->commentRepository->getById($id);
                } else {
                    $postData['entity_id'] = null;
                }

                if (!$model->getId() && $id) {
                    throw new LocalizedException(__('This Comment no longer exists.'));
                }
                $this->extractPostData($model, $postData);
                $this->_eventManager->dispatch(
                    'blog_comment_prepare_save',
                    ['model' => $model, 'request' => $this->getRequest()]
                );
                $this->commentRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved Comment'));

                $this->cacheInvalidator->invalidate();

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check permissions for this action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogcomment');
    }
}
