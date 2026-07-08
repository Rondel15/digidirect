<?php 

namespace Digidirect\Customer\Controller\Account;

use Magento\Framework\Controller\ResultFactory;

class GetPaIdSalesForce extends \Magento\Framework\App\Action\Action {
    
    protected $request;
    
    protected $customerRepository;
    
    protected $customerSession;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession
    ) 
    {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
    }

    public function execute(){
        if ($this->getRequest()->getPost('pa_id')):
            $pa_id = $this->getRequest()->getPost('pa_id');
            $data = array($pa_id);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($data);
            $customerID = $this->customerSession->getCustomer()->getId();
            $customer = $this->customerRepository->getById($customerID);
            $customer->setCustomAttribute('pa_id', $pa_id);
            $this->customerRepository->save($customer);
            
            return $resultJson;
        endif;
    }
    
}