<?php

namespace Digidirect\ParticularAudienceAPI\Block\Widget;


class TestEventsApi extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_template = 'Digidirect_ParticularAudienceAPI::widget/test-events-api.phtml';
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,    
        array $data = []
    ) {        
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }
}
