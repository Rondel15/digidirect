<?php

namespace Digidirect\Blog\Controller\Index;

use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Helper\Design;
use Digidirect\Blog\Model\BlogDesign;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
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
     * @param BlogDesign $blogDesign
     * @param Design $designHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        BlogDesign $blogDesign,
        Design $designHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->resultPageFactory = $resultPageFactory;
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
        if (!$this->dataHelper->isModuleEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $this->blogDesign->setNewTheme();

        $page = $this->resultPageFactory->create();

        $layoutUpdate = $this->designHelper->getXmlUpdates();
        if (!empty($layoutUpdate)) {
            $page->addUpdate($layoutUpdate);
            $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
        }

        $searchQuery = $this->getRequest()->getParam('s');
        $title = !empty($searchQuery) ? __("Search results for: '%1'", $searchQuery) : __('Latest Blog Posts');
        $page->getConfig()->getTitle()->set($title);
        $breadcrumbShow = $this->dataHelper->getGeneralSettingsConfig('breadcrumb');
        if ($breadcrumbShow) {
            $this->addBreadcrumb($page, $title);
        }

        $pageLayout = $this->dataHelper->getGeneralSettingsConfig('post_list_layout');
        $pageConfig = $page->getConfig();
        $pageConfig->setPageLayout($pageLayout);

        return $page;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     * @param mixed $title
     * @return mixed
     */
    private function addBreadcrumb(\Magento\Framework\View\Result\Page $page, $title)
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
                'link' => $this->_url->getUrl(''),
            ]
        );
        $breadcrumbs->addCrumb(
            'digidirect_blog',
            [
                'label' => __($title),
                'title' => __($title),
            ]
        );
    }
}
