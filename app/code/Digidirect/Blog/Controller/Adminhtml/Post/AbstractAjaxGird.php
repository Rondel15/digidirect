<?php

namespace Digidirect\Blog\Controller\Adminhtml\Post;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\PostRepository;
use Digidirect\Blog\Registry\CurrentPostItem;
use Magento\Framework\Registry;
use Digidirect\Blog\Model\Post as PostModel;
use Digidirect\Blog\Model\PostFactory as PostModelFactory;

/**
 * Class AbstractAjaxGird
 */
abstract class AbstractAjaxGird extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * @var PostModelFactory
     */
    protected $postFactory;

    /**
     * @var CurrentPostItem
     */
    protected $currentPostItem;

    /**
     * AbstractAjaxGird constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Registry $registry
     * @param PostRepository $postRepository
     * @param PostModelFactory $postFactory
     * @param CurrentPostItem $currentPostItem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        PostRepository $postRepository,
        PostModelFactory $postFactory,
        CurrentPostItem $currentPostItem
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultPageFactory = $resultPageFactory;
        $this->postRepository = $postRepository;
        $this->postFactory = $postFactory;
        $this->currentPostItem = $currentPostItem;
    }

    /**
     * Grid Data for AJAX request
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = (int)$request->getParam('id', null);
        $model = $this->getCurrentEntity($id);
        $this->currentPostItem->set($model);
        $this->_view->loadLayout()->getLayout()->getBlock($this->getBlockName());
        $this->_view->renderLayout();
    }

    /**
     * @param int|null $id
     * @return PostModel
     */
    protected function getCurrentEntity($id)
    {
        if ($id) {
            return $this->postRepository->getById($id);
        }

        return $this->postFactory->create();
    }

    /**
     * @return string
     */
    abstract protected function getBlockName();
}
