<?php

namespace Digidirect\Blog\Block;

use Digidirect\Blog\Api\CommentRepositoryInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Helper\Arrow;
use Digidirect\Blog\Helper\Category as CategoryHelper;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\Config\Provider\PageListType;
use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\Post;
use Digidirect\Blog\Model\UrlModel;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Blog extends Template
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var CommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CategoryHelper
     */
    protected $categoryHelper;

    /**
     * @var Arrow
     */
    protected $arrowHelper;

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var bool
     */
    protected $_isShowCategoryNames = null;

    protected $_urlInterface;
    
    protected $logger;
    
    /**
     * Blog constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     * @param PostRepositoryInterface $postRepository
     * @param CommentRepositoryInterface $commentRepository
     * @param FilterProvider $filterProvider
     * @param Registry $registry
     * @param CategoryHelper $categoryHelper
     * @param Arrow $arrowHelper
     * @param UrlModel $urlModel
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        PostRepositoryInterface $postRepository,
        CommentRepositoryInterface $commentRepository,
        FilterProvider $filterProvider,
        Registry $registry,
        CategoryHelper $categoryHelper,
        Arrow $arrowHelper,
        UrlModel $urlModel,
        \Magento\Framework\UrlInterface $urlInterface,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->postRepository = $postRepository;
        $this->commentRepository = $commentRepository;
        $this->filterProvider = $filterProvider;
        $this->registry = $registry;
        $this->categoryHelper = $categoryHelper;
        $this->arrowHelper = $arrowHelper;
        $this->urlModel = $urlModel;
        $this->_urlInterface = $urlInterface;
        $this->logger = $logger;
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Post\Collection
     */
    public function getCollection()
    {
        if (!$this->hasData('collection')) {
            $collection = $this->prepareCollection();
            $this->setData('collection', $collection);
            if ($this->isShowCategoryNames()) {
                $this->categoryHelper->prepareCategoriesUrls();
            }
        }
        return $this->getData('collection');
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Post\Collection
     */
    protected function prepareCollection()
    {
        $this->arrowHelper->clearCondition();
        $collection = $this->postRepository->getPostList(
            Status::STATUS_ENABLED,
            $this->_storeManager->getStore()->getId(),
            date('Y-m-d')
        );
        $collection->setOrder(
            PostInterface::FIELD_PUBLISH_DATE,
            $this->dataHelper->getGeneralSettingsConfig('post_sorting')
        );
        $collection->setOrder(
            PostInterface::FIELD_ID,
            $this->dataHelper->getGeneralSettingsConfig('post_sorting')
        );
        $search = $this->getRequest()->getParam('s');
        if ($search) {
            $collection->addFieldToFilter(
                ['title', 'short_content', 'content'],
                [
                    ['like' => '%' . $search . '%'],
                    ['like' => '%' . $search . '%'],
                    ['like' => '%' . $search . '%'],
                ]
            );
            $this->arrowHelper->setSearchCondition($search);
        }
        return $collection;
    }

    /**
     * @return bool
     */
    public function isListPageMode()
    {
        return $this->dataHelper->getDisplaySettingsConfig('list_page/show_type') == PageListType::TYPE_LIST;
    }

    /**
     * @param int $postId
     * @return string
     */
    public function getComments($postId)
    {
        return $this->commentRepository->getCountByPostId($postId);
    }

    /**
     * @param Post $post
     * @return mixed
     */
    public function getShortDescription(Post $post)
    {
        return $this->filterProvider->getBlockFilter()->filter($post->getShortContent());
    }

    /**
     * Prepare faq list toolbar
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager $toolbar */
        $toolbar = $this->getLayout()->getBlock('post_list_pager.toolbar');
        if ($toolbar) {
            $postPerPage = $this->getPostPerPage();

            $toolbar->setLimit($postPerPage)->setShowPerPage(true);
            $collection = $this->getCollection();

            $toolbar->setCollection($collection);
            $this->setChild('blog_toolbar', $toolbar);
        }

        return $this;
    }

    public function showPager(){
        $pager = false;

        $collection = $this->getCollection();
        $total_size = $collection->getSize();
        $per_page = $this->dataHelper->getPostPerPage();

        if($total_size > $per_page){
            $pager = true;
        }

        return $pager;
    }

    public function getPagerHtml(){
        $pager = "";

        $collection = $this->getCollection();
        $total_size = $collection->getSize();
        $per_page = $this->dataHelper->getPostPerPage();

        $total_pages = ceil($total_size/$per_page);

        $current_page_number = 1;
        $page_number_param = $this->getRequest()->getParams("p");

        if(isset($page_number_param["p"])){
            $current_page_number = $page_number_param["p"];
        }

        //To do GET Correct URL
//        $currentUrl = $this->urlModel->getBlogListUrl(true);

        $initialUrl = $this->_urlInterface->getCurrentUrl(false);

        $currentUrl = strtok($initialUrl, "?");

        if($total_pages > 1){
            for ($x = 1; $x <= $total_pages; $x++) {
                $current_item = "";
                $page_number = "<a href='".$currentUrl."?p=".$x."'>".$x."</a>";

                if($current_page_number == $x){
                    $current_item = "current";
                    $page_number = "<span>".$x."</span>";
                }

                $pager .= "<li class=\"item $current_item\"><strong class=\"page\">". $page_number ."</strong></li>";
            }
        }

        return $pager;
    }

    /**
     * @return number
     */
    protected function getPostPerPage()
    {
        $limit = $this->getLimit();
        if (!$limit) {
            $limit = $this->dataHelper->getPostPerPage();
        }
        return $limit;
    }

    /**
     * @return bool
     */
    public function isCommentsEnabled()
    {
        return $this->dataHelper->getCommentSettingsConfig('type_of_comment');
    }

    /**
     * @return bool
     */
    public function isDisplayTagEnabled()
    {
        return $this->dataHelper->getDisplaySettingsConfig('list_page/display_tags_listing');
    }

    /**
     * @param string $tagName
     * @return string
     */
    public function getTagUrl($tagName)
    {
        return $this->urlModel->getTagUrl($tagName);
    }

    /**
     * @param PostInterface $post
     * @return \Digidirect\Blog\Model\Category[]
     */
    public function getCategoriesTitlesAsArray(PostInterface $post)
    {
        $categoryIds = (array)$post->getData('category_id');

        $categories = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryHelper->getCategory($categoryId);
            if (!empty($category)) {
                $categories[] = $this->categoryHelper->getCategory($categoryId);
            }
        }

        return $categories;
    }

    /**
     * Should post's categories names be included in the view or not.
     *
     * @return bool
     */
    public function isShowCategoryNames()
    {
        if ($this->_isShowCategoryNames === null) {
            $this->_isShowCategoryNames = $this->getData('show_category_name') && $this->isShowCategoryNamesSetting();
        }
        return $this->_isShowCategoryNames;
    }

    /**
     * Return value of the corresponding 'Show category name' config option.
     *
     * @return bool
     */
    public function isShowCategoryNamesSetting()
    {
        return (bool)$this->dataHelper->getDisplaySettingsConfig('list_page/show_category_name');
    }

    /**
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        $cacheLifetime = parent::getCacheLifetime();
        if (!$cacheLifetime) {
            $cacheLifetime = 86400;
        }

        return $cacheLifetime;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        $cacheKey['store-code'] = $this->_storeManager->getStore()->getCode();
        return $cacheKey;
    }
}
