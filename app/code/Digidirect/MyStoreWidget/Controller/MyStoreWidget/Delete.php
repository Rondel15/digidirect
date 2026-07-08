<?php
namespace Digidirect\MyStoreWidget\Controller\MyStoreWidget;

use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\MyStoreWidget\Model\MyStoreFactory;
use Digidirect\MyStoreWidget\Helper\Data as MyStoreDataHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

/**
 * Class Delete
 * @package Digidirect\MyStoreWidget\Controller\MyStoreWidget
 */
class Delete extends Index
{
    /**
     * @var MyStoreDataHelper
     */
    protected $myStoreDataHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param \Digidirect\MyStoreWidget\Model\MyStoreFactory $myStoreFactory
     * @param Session $customerSession
     * @param MyStoreDataHelper $myStoreDataHelper
     */
    public function __construct(
        Context $context,
        MyStoreRepositoryInterface $myStoreRepository,
        MyStoreFactory $myStoreFactory,
        Session $customerSession,
        MyStoreDataHelper $myStoreDataHelper
    ) {
        parent::__construct(
            $context,
            $myStoreRepository,
            $myStoreFactory,
            $customerSession
        );
        $this->myStoreDataHelper = $myStoreDataHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $customerId = $customerId = $this->_getSession()->getCustomerId();
        if ($customerId) {
            try {
                $this->myStoreRepository->deleteByCustomerId($customerId);
                $this->customerSession->unsAbstractEntityId();
                $this->myStoreDataHelper->setMyStoreCookie(0);
                $this->messageManager->addSuccessMessage(__('You deleted the record.'));
                return $this->_redirect('customer/account/index');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->_redirect('customer/account/index');
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a record to delete.'));
        return $this->_redirect('customer/account/index');
    }
}
