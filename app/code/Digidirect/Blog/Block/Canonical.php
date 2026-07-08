<?php

namespace Digidirect\Blog\Block;

class Canonical extends \Magento\Framework\View\Element\Template
{
    protected $_storeManager;
    
    protected $_urlInterface;
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Framework\UrlInterface $urlInterface,    
        array $data = []
    )
    {        
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getCurrentUrl()
    {
        return rtrim($this->_urlInterface->getCurrentUrl(), '/');
    }
    
}
?>