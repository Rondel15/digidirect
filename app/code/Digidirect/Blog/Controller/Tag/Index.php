<?php

namespace Digidirect\Blog\Controller\Tag;

use Digidirect\Blog\Api\Data\TagInterface;
use Digidirect\Blog\Api\TagRepositoryInterface;
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
     * @var TagRepositoryInterface
     */
    protected $tagRepository;

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
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $dataHelper
     * @param TagRepositoryInterface $tagRepository
     * @param Registry $registry
     * @param BlogDesign $blogDesign
     * @param Design $designHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        TagRepositoryInterface $tagRepository,
        Registry $registry,
        BlogDesign $blogDesign,
        Design $designHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->tagRepository = $tagRepository;
        $this->registry = $registry;
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
        $tag = $this->initTag();
        $page = $this->resultPageFactory->create();
        $this->blogDesign->setNewTheme();

        $layoutUpdate = $this->designHelper->getXmlUpdates();
        if (!empty($layoutUpdate)) {
            $page->addUpdate($layoutUpdate);
            $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
        }

        $page->getConfig()->getTitle()->set(__('Blog Posts with tag #%1', $tag->getName()));
        $breadcrumbShow = $this->dataHelper->getGeneralSettingsConfig('breadcrumb');
        if ($breadcrumbShow) {
            $this->addBreadcrumbs($page);
        }
        $pageLayout = $this->dataHelper->getGeneralSettingsConfig('post_list_layout');
        $pageConfig = $page->getConfig();
        $pageConfig->setPageLayout($pageLayout);
        $page->getLayout()->getUpdate();
        return $page;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     * @return void
     */
    private function addBreadcrumbs(\Magento\Framework\View\Result\Page $page)
    {
        /**
         * @var $breadcrumbs
         */
        $breadcrumbs = $page->getLayout()->getBlock('breadcrumbs');
        if (!$breadcrumbs) {
            return;
        }
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Home'),
                'link' => $this->_url->getUrl(''),
            ]
        );
    }

    /**
     * @return \Digidirect\Blog\Model\Tag
     * @throws NotFoundException
     */
    protected function initTag()
    {
        $tagName = $this->getRequest()->getParam('tag');
        $tag = $this->tagRepository->getByName(urldecode($tagName));
        if (!$tag->getId()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $this->registry->register(TagInterface::CURRENT_ITEM, $tag);
        return $tag;
    }
}
