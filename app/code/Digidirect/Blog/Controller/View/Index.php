<?php
namespace Digidirect\Blog\Controller\View;

use Digidirect\Blog\Api\CategoryRepositoryInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Helper\Arrow;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Helper\Design;
use Digidirect\Blog\Model\BlogDesign;
use Digidirect\Blog\Model\PostRepository;
use Digidirect\Blog\Model\UrlModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Index extends Action
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PostRepository
     */
    protected $postRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Arrow
     */
    protected $arrowHelper;

    /**
     * @var BlogDesign
     */
    protected $blogDesign;

    /**
     * @var Design
     */
    protected $designHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $dataHelper
     * @param PostRepository $postRepository
     * @param Registry $registry
     * @param UrlModel $urlModel
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Arrow $arrowHelper
     * @param BlogDesign $blogDesign
     * @param Design $designHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        PostRepository $postRepository,
        Registry $registry,
        UrlModel $urlModel,
        CategoryRepositoryInterface $categoryRepository,
        Arrow $arrowHelper,
        BlogDesign $blogDesign,
        Design $designHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataHelper = $dataHelper;
        $this->postRepository = $postRepository;
        $this->registry = $registry;
        $this->urlModel = $urlModel;
        $this->categoryRepository = $categoryRepository;
        $this->arrowHelper = $arrowHelper;
        $this->blogDesign = $blogDesign;
        $this->designHelper = $designHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $this->blogDesign->setNewTheme();

        $layoutUpdate = $this->designHelper->getXmlUpdates();
        if (!empty($layoutUpdate)) {
            $page->addUpdate($layoutUpdate);
            $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
        }

        $post = $this->initPost();
        $metaTitle = $post->getMetaTitle() ? $post->getMetaTitle() : $post->getTitle();
        $page->getConfig()->getTitle()->set($metaTitle);
        $page->getConfig()->setKeywords($post->getMetaKeywords());
        $page->getConfig()->setDescription($post->getMetaDescription());

        $breadcrumbShow = $this->dataHelper->getGeneralSettingsConfig('breadcrumb');
        if ($breadcrumbShow) {
            $this->addBreadCrumbs($page, $post);
        }
        $pageLayout = $this->dataHelper->getGeneralSettingsConfig('post_layout');
        $pageConfig = $page->getConfig();
        $pageConfig->setPageLayout($pageLayout);
        $page->getLayout()->getUpdate();
        return $page;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     * @param \Digidirect\Blog\Model\Post $post
     * @return void
     */
    protected function addBreadCrumbs(\Magento\Framework\View\Result\Page $page, \Digidirect\Blog\Model\Post $post)
    {
        $breadcrumbs = $page->getLayout()->getBlock('breadcrumbs');
        if (!$breadcrumbs) {
            return;
        }
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl('')
            ]
        );
        
        $targetCategory = null;
        if ($this->arrowHelper->isCategoryCondition()) {
            $targetCategory = $this->categoryRepository->getById($this->arrowHelper->getParams());
        } elseif (($urlKey = $this->urlModel->getCategoryUrlKey($this->_redirect->getRefererUrl()))
        ) {
            $targetCategory = $this->categoryRepository->getByUrlKey($urlKey);
        }

        if ($targetCategory !== null) {
            if (!$targetCategory->getId()) {
                $targetCategory = $this->getCategoryByPostId($post->getId());
            }
            if ($targetCategory->getId()) {
                $parentCategories = $this->categoryRepository->getParentCategories($targetCategory);
                foreach ($parentCategories as $parentCategory) {
                    $breadcrumbs->addCrumb(
                        'category' . $parentCategory->getId(),
                        [
                            'label' => $parentCategory->getName(),
                            'title' => __($parentCategory->getName()),
                            'link' => $parentCategory->getViewUrl()
                        ]
                    );
                }
            }
        } else {
            $breadcrumbs->addCrumb(
                'digidirect_blog',
                [
                    'label' => __('Latest Blog Posts'),
                    'title' => __('Latest Blog Posts'),
                    'link' => $this->urlModel->getBlogListUrl()
                ]
            );
        }
        $breadcrumbs->addCrumb(
            'post',
            [
                'label' => __($post->getTitle()),
                'title' => __($post->getTitle()),
                'link' => ''
            ]
        );
    }

    /**
     * @param int $postId
     * @return \Digidirect\Blog\Model\Category
     */
    protected function getCategoryByPostId($postId)
    {
        $categoryId = $this->postRepository->getCategoryId($postId);
        return $this->categoryRepository->getById($categoryId);
    }

    /**
     * @return \Digidirect\Blog\Model\Post
     * @throws NotFoundException
     */
    protected function initPost()
    {
        $postId = $this->getRequest()->getParam('post_id');
        $post = $this->postRepository->getById($postId);
        if (!$post->getId() || !$post->isActive()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $this->registry->register(PostInterface::CURRENT_ITEM, $post);
        $this->postRepository->updateView($post);
        return $post;
    }
}
