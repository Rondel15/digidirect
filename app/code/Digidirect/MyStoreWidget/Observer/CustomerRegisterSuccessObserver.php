<?php
namespace Digidirect\MyStoreWidget\Observer;

use Digidirect\MyStoreWidget\Model\MyStoreFactory;
use Digidirect\MyStoreWidget\Api\Data\MyStoreInterface;
use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Magento\Customer\Model\Session as CustomerModelSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var MyStoreFactory
     */
    protected $myStoreFactory;

    /**
     * @var MyStoreRepositoryInterface
     */
    protected $myStoreRepository;

    /**
     * @var CustomerModelSession
     */
    protected $customerSession;

    /**
     * @param MyStoreFactory $myStoreFactory
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param CustomerModelSession $customerSession
     */
    public function __construct(
        MyStoreFactory $myStoreFactory,
        MyStoreRepositoryInterface $myStoreRepository,
        CustomerModelSession $customerSession
    ) {
        $this->myStoreFactory = $myStoreFactory;
        $this->myStoreRepository = $myStoreRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if ($entityId = $this->customerSession->getAbstractEntityId()) {
            /** @var MyStoreInterface $myStore */
            $myStore = $this->myStoreFactory->create();
            $myStore->setAbstractEntityId($entityId);
            $myStore->setCustomerId($observer->getCustomer()->getId());
            $this->myStoreRepository->save($myStore);
        }
        return $this;
    }
}
