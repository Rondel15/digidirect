<?php
namespace Digidirect\Blog\Controller\Adminhtml\Comment;

use Digidirect\Blog\Api\Data\CommentInterface;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Digidirect\Blog\Model\CommentFactory;
use Digidirect\Blog\Model\CommentRepository;

/**
 * Class Edit
 */
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CommentFactory
     */
    protected $commentFactory;

    /**
     * @var CommentRepository
     */
    protected $commentRepository;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param CommentFactory $commentFactory
     * @param CommentRepository $commentRepository
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        CommentFactory $commentFactory,
        CommentRepository $commentRepository
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->registry             = $registry;
        $this->commentFactory      = $commentFactory;
        $this->commentRepository   = $commentRepository;
        parent::__construct($context);
    }

    /**
     * @return $this|\Magento\Framework\View\Result\Page
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = (int)$request->getParam('id', 0);

        /**
         * @var $model \Digidirect\Blog\Model\Comment
         */
        $model = $this->commentFactory->create();
        if ($id) {
            $model = $this->commentRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This comment item doesn\'t exist'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->registry->register(CommentInterface::CURRENT_ITEM, $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Digidirect_Blog::blogcomment')
            ->addBreadcrumb(__('Digidirect Comment Item'), __('Digidirect Comment Item'))
            ->addBreadcrumb(
                $id ? __('Edit Comment Item') : __('Edit Comment Item'),
                $id ? __('Edit Comment Item') : __('New Comment')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('Comment Management'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? 'Edit Comment Item' : __('New Comment'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogcomment');
    }
}
