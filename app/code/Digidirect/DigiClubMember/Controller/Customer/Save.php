<?php

declare(strict_types=1);

namespace Digidirect\DigiClubMember\Controller\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Customers digiClub subscription save controller
 */
class Save extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface, HttpGetActionInterface
{
    const DIGICLUB_GROUP_ID = 10;
    
    const GENERAL_GROUP_ID = 1;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;
    
    protected $customerSession;
    
    protected $logger;
    
    protected $cacheTypeList;
    
    protected $cacheFrontendPool;

    protected $urlInterface;
    
    protected $redirect;
    
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CustomerRepository $customerRepository
     * @param SubscriptionManagerInterface $subscriptionManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        CustomerRepository $customerRepository
    ) {
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->redirect = $redirect;
        parent::__construct($context);
    }

    /**
     * Save digiClub subscription preference action
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('customer/account');
        }

        $customerId = $this->customerSession->getCustomerId();
        if ($customerId === null) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving your subscription.'));
        } else {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $storeId = (int)$this->storeManager->getStore()->getId();
                $customer->setStoreId($storeId);
                $customerGroupId = $customer->getGroupId();
                $currentUrl = rtrim($this->urlInterface->getCurrentUrl(), '/');
                
                $this->logger->info('$currentUrl: ' . $currentUrl);
                
                $refererUrl = $this->redirect->getRefererUrl();
                $this->logger->info('$refererUrl: ' . $refererUrl);
                
                $isDigiClubParam = (boolean)$this->getRequest()->getParam('is_digiclub', false);
                $customerFirstName = $this->getRequest()->getParam('digiclub-firstname');
                $customerLastName = $this->getRequest()->getParam('digiclub-lastname');
                $customerEmail = $this->getRequest()->getParam('digiclub-email');
                $customerContactNumber = $this->getRequest()->getParam('digiclub-contact-number');
                $customerDob = $this->getRequest()->getParam('digiclub-dob');
                
                //$this->logger->info('$customerFirstName: ' . $customerFirstName);
                //$this->logger->info('$customerLastName: ' . $customerLastName);
                //$this->logger->info('$customerEmail: ' . $customerEmail);
                //$this->logger->info('$customerContactNumber: ' . $customerContactNumber);
                //$this->logger->info('$customerDob: ' . $customerDob);
                
                $this->setIgnoreValidationFlag($customer);
                
                if ($isDigiClubParam) {
                    $customer->setGroupId(self::DIGICLUB_GROUP_ID);
                } else {
                    $customer->setGroupId(self::GENERAL_GROUP_ID);
                }
                
                $customer->setData('firstname', $customerFirstName);
                $customer->setData('lastname', $customerLastName);
                $customer->setData('email', $customerEmail);
                $customer->setCustomAttribute('contact_number', $customerContactNumber);
                if ($customerDob) {
                    $customer->setData('dob', $customerDob);
                }
                //$customer->setData('contact_number', $customerContactNumber);
                
                $this->customerRepository->save($customer);
                
                if ($isDigiClubParam) {
                    /*if ($refererUrl == "https://www.digidirect.com.au/digiclubmember/customer/index/digiclub/competition") {
                        return $this->_redirect('digiclubcompetition');
                    }*/
                    return $this->_redirect('digiclubmember/customer/thankyou');
                } else {
                    $this->messageManager->addSuccess(__('We have updated your digiClub subscription.'));
                }
                
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving your subscription.'));
        }
    }
        return $this->_redirect('digiclubmember/customer/index');
    }

    /**
     * Set ignore_validation_flag to skip unnecessary address and customer validation
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function setIgnoreValidationFlag(CustomerInterface $customer): void
    {
        $customer->setData('ignore_validation_flag', true);
    }
}
