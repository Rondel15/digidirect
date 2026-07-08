<?php

namespace Digidirect\Blog\Controller\Archive;

use Digidirect\Blog\Block\Archive;
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
     * @param Registry $registry
     * @param BlogDesign $blogDesign
     * @param Design $designHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $dataHelper,
        Registry $registry,
        BlogDesign $blogDesign,
        Design $designHelper
    ) {
        $this->dataHelper = $dataHelper;
        $this->resultPageFactory = $resultPageFactory;
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
        $date = $this->getRequest()->getParam('date');
        $date = explode('-', $date);
        $date[2] = '01';
        $time = strtotime(implode('-', $date));

        if (!$time || count($date) != 3) {
            throw new NotFoundException(__('Page not found.'));
        }

        $this->registry->register(Archive::CURRENT_YEAR, (int)$date[0]);
        $this->registry->register(Archive::CURRENT_MONTH, (int)$date[1]);

        $page = $this->resultPageFactory->create();
        $this->blogDesign->setNewTheme();

        $layoutUpdate = $this->designHelper->getXmlUpdates();
        if (!empty($layoutUpdate)) {
            $page->addUpdate($layoutUpdate);
            $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
        }

        $page->getConfig()->getTitle()->set(__('Monthly Archives: ' . date('F', $time) . ' ' . date('Y', $time)));
        $breadcrumbShow = $this->dataHelper->getGeneralSettingsConfig('breadcrumb');
        if ($breadcrumbShow) {
            $this->addBreadcrumbs($page);
        }
        return $page;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     * @return mixed
     */
    private function addBreadcrumbs(\Magento\Framework\View\Result\Page $page)
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
                'label' => __('Latest Blog Posts'),
                'title' => __('Latest Blog Posts'),
            ]
        );
    }
}
