<?php

namespace Digidirect\DigiClubCompetition\Block;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;

class Index extends Template
{
    /**
    * @var \Magento\Framework\App\Response\RedirectInterface
    */
    protected $redirect;

    /**
    * Customer session
    *
    * @var \Magento\Customer\Model\Session
    */
    protected $_customerSession;
    
    protected $urlInterface;
    
    protected $logger;
    
    protected $registry;
    
    protected $request;
    
    protected $storeManager;
    
    protected $categoryRepository;
    
    protected $customerRepository;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,    
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        CustomerRepository $customerRepository,
        array $data = []
            
    ) {
        $this->_customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->urlInterface = $urlInterface;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $data);
    }
    
    public function checkCustomerSession() {
        $this->logger->info('checkCustomerSession');
        return $this->_customerSession->isLoggedIn();
    }
    
    public function checkCustomerGroup() {
        $this->logger->info('checkCustomerGroup');
        if ($this->checkCustomerSession()) {
            $customerId = $this->_customerSession->getCustomerId();
            $customer = $this->customerRepository->getById($customerId);
            $storeId = (int)$this->storeManager->getStore()->getId();
            $customer->setStoreId($storeId);
            $customerGroupId = $customer->getGroupId();

            return $customerGroupId;
        }
        return false;
    }
    
    
}