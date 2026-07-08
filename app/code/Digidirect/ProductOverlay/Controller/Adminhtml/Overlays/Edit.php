<?php

namespace Digidirect\ProductOverlay\Controller\Adminhtml\Overlays;

use Digidirect\ProductOverlay\Model\Overlays;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Edit
 * @package Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
 */
class Edit extends \Digidirect\ProductOverlay\Controller\Adminhtml\Overlays
{
    /**
     * Overlay Edit Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Digidirect_ProductOverlay::overlay');
        $resultPage->getConfig()->getTitle()->prepend(__('Digidirect Product Overlays'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Overlays'));
        $id = (int)$this->getRequest()->getParam('id');
        $resultPage->addBreadcrumb(__('Digidirect'), __('Digidirect'))
            ->addBreadcrumb(__('Manage Product Overlays'), __('Manage Product Overlays'));

        // set title and breadcrumbs
        $title = $id ? __('Edit Product Overlay') : __('New Product Overlay');

        if (!empty($title)) {
            $resultPage->addBreadcrumb($title, $title);
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Product Overlays'));
        
        /** @var \Digidirect\ProductOverlay\Model\Overlays $overlay */
        if ($id) {
            $overlay = $this->_overlayRepository->getById($id);
            if (!$overlay->getId()) {
                $this->messageManager->addErrorMessage(__('This item no longer exists.'));
                $this->_redirect('digidirect_productoverlay/*');
                return;
            }
        } else {
            $overlay = $this->_overlayFactory->create();
        }
        $resultPage->getConfig()->getTitle()->prepend($id ? $overlay->getName() : __('New Product Overlay'));

        // set entered data if was error when we do save
        $data = $this->_getSession()->getPageData(true);
        if (!empty($data)) {
            $overlay->addData($data);
        }
        $this->_coreRegistry->register(Overlays::CURRENT_OVERLAY_REGISTRY, $overlay);
        $this->_initAction();
        return $resultPage;
    }
}
