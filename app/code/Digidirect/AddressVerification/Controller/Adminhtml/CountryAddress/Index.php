<?php
namespace Digidirect\AddressVerification\Controller\Adminhtml\CountryAddress;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Import
 * @package Digidirect\AddressVerification\Controller\Adminhtml\Index
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Digidirect_AddressVerification::address_verification');
        $resultPage->addBreadcrumb(
            __('Address Autocomplete'),
            __('Address Autocomplete')
        );
        $resultPage->addBreadcrumb(
            __('Country Address Attributes'),
            __('Country Address Attributes')
        );
        $resultPage->getConfig()
            ->getTitle()->prepend(__('Manage Country Address Attributes'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AddressVerification::address_verification');
    }
}
