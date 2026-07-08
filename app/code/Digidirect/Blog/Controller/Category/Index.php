<?php
namespace Digidirect\Blog\Controller\Category;

use Digidirect\Blog\Api\CategoryRepositoryInterface;
use Digidirect\Blog\Api\Data\CategoryInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Helper\Design;
use Digidirect\Blog\Model\BlogDesign;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
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
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Registry
     */
    protected $registry;

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
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Registry $registry
     * @param BlogDesign $blogDesign
     * @param Design $designHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        CategoryRepositoryInterface $categoryRepository,
        Registry $registry,
        BlogDesign $blogDesign,
        Design $designHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->categoryRepository = $categoryRepository;
        $this->registry = $registry;
        $this->blogDesign = $blogDesign;
        $this->designHelper = $designHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $category = $this->initCategory();
        $page = $this->resultPageFactory->create();
        $this->blogDesign->setNewTheme();

        $layoutUpdate = $this->designHelper->getXmlUpdates();
        if (!empty($layoutUpdate)) {
            $page->addUpdate($layoutUpdate);
            $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
        }

        $metaTitle = $category->getMetaTitle() ? $category->getMetaTitle() : $category->getName();
        $page->getConfig()->getTitle()->set($metaTitle);
        $page->getConfig()->setKeywords($category->getMetaKeywords());
        $page->getConfig()->setDescription($category->getMetaDescription());

        $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($category->getName());
        }
        $breadcrumbShow = $this->dataHelper->getGeneralSettingsConfig('breadcrumb');
        if ($breadcrumbShow) {
            $breadcrumbs = $page->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbs) {
                $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Home'),
                        'link' => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbs->addCrumb(
                    'category',
                    [
                        'label' => __($category->getName()),
                        'title' => __($category->getName()),
                    ]
                );
            }
        }

        $pageLayout = $this->dataHelper->getGeneralSettingsConfig('cat_layout');
        $pageConfig = $page->getConfig();
        $pageConfig->setPageLayout($pageLayout);
        $page->getLayout()->getUpdate();
        return $page;
    }

    /**
     * @return \Magento\Framework\Model\AbstractModel
     * @throws NotFoundException
     */
    protected function initCategory()
    {
        $categoryId = $this->getRequest()->getParam('category_id');
        $category = $this->categoryRepository->getById($categoryId);
        if (!$category->getId() || !$category->getStatus()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $this->registry->register(CategoryInterface::CURRENT_ITEM, $category);
        return $category;
    }
}
