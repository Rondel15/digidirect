<?php
namespace Digidirect\Blog\Controller\Adminhtml\Post;

use Digidirect\Blog\Api\Data\PostInterface;
use Magento\Framework\Registry;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Digidirect\Blog\Model\PostFactory;
use Digidirect\Blog\Model\PostRepository;

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
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * Edit constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param PostRepository $postRepository
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        PostFactory $postFactory,
        PostRepository $postRepository
    ) {
        $this->resultPageFactory    = $resultPageFactory;
        $this->registry             = $registry;
        $this->postFactory          = $postFactory;
        $this->postRepository       = $postRepository;
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
         * @var $model \Digidirect\Blog\Model\Post
         */
        $model = $this->postFactory->create();
        if ($id) {
            $model = $this->postRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This post item doesn\'t exist'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->registry->register(PostInterface::CURRENT_ITEM, $model);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Digidirect_Blog::blogpost')
            ->addBreadcrumb(__('Digidirect Post Item'), __('Digidirect Post Item'))
            ->addBreadcrumb(
                $id ? __('Edit Post Item') : __('Edit Post Item'),
                $id ? __('Edit Post Item') : __('New Post Item')
            );
        $resultPage->getConfig()->getTitle()->prepend(__('Post Management'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? 'Edit Post Item' : __('New Post Item'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_Blog::blogpost');
    }
}
