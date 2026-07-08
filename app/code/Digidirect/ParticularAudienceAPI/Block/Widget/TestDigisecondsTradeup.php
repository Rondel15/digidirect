<?php

namespace Digidirect\ParticularAudienceAPI\Block\Widget;


class TestDigisecondsTradeup extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $customer;
    
    protected $_template = 'Digidirect_ParticularAudienceAPI::widget/test-digiseconds-tradeup.phtml';
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context, 
        \Magento\Customer\Model\Session $customerSession,   
        array $data = []
    ) {        
        $this->customer = $customerSession;
        parent::__construct($context, $data);
    }
    
    public function checkCustomer() {
        return $this->customer;
    }
}