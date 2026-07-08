<?php

namespace Digidirect\ProductOverlay\Controller\Adminhtml\Overlays;

/**
 * Class Index
 * @package Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
 */
class Index extends \Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
{
    /**
     * Overlay Index Action. Shows overlays list
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Digidirect_ProductOverlay::overlay');
        $resultPage->getConfig()->getTitle()->prepend(__('Digidirect Product Overlays'));
        $resultPage->addBreadcrumb(__('Digidirect'), __('Digidirect'));
        $resultPage->addBreadcrumb(__('Product Overlays'), __('Product Overlays'));
        return $resultPage;
    }
}
