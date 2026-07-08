<?php
namespace Digidirect\ParticularAudienceAPI\Block;

class PdpFrequentlyBoughtWith extends \Magento\Framework\View\Element\Template
{
    protected $productCollectionFactory;
    
    protected $_registry;
    
    protected $variable;
    
    protected $curl;
    
    protected $jsonSerializer;
    
    protected $logger;
    
    protected $cookieManager;
    
    protected $cookieMetadataFactory;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,    
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        array $data = []
    ) {        
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_registry = $registry;
        $this->variable = $variable;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->listProductBlock = $listProductBlock;
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        parent::__construct($context, $data);
    }
    
    public function _prepareLayout() {
        return parent::_prepareLayout();
    }
    
    public function getCurrentProduct(){         
        return $this->_registry->registry('current_product');
    }
    
    public function getRecommendedProducts(){
        
        //Get token from custom variable
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');
        $paWidgetId = 'ca223b70-fc3d-ec11-aae9-02dca44cceec';
        
        $currentProductId = $this->getCurrentProduct()->getId();
        //$this->logger->info("currentProductId: " . $currentProductId); 
        if ($currentProductId) {
            $refIdParam = "&refId=".$currentProductId;
        } else {
            $refIdParam = "";
        }
        
        $customerId = $this->cookieManager->getCookie('pa_customer_id');
        //$this->logger->info("customerId: " . $customerId); 
        if ($customerId) {
            $customerIdParam = "&customerId=".$customerId;
        } else {
            $customerIdParam = "";
        }
        
        $getRecommendationsUrl = "https://api-recs.particularaudience.com/3.0/recommendations?currentUrl=https://www.digidirect.com.au/pa-digi-products-pdp&expandProductDetails=false".$refIdParam.$customerIdParam;
        //$this->logger->info("getRecommendationsUrl: " . $getRecommendationsUrl); 
        
        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
        $this->curl->get($getRecommendationsUrl);
        
        $getRecommendationsResult = $this->curl->getBody();
        $getRecommendationsResultJson = $this->jsonSerializer->unserialize($getRecommendationsResult);
        
        $widgets = $getRecommendationsResultJson['recommendations']['route']['widgets'];
        
        foreach($widgets as $key=>$value) {
            $widgetId = $value['id'];
            if ($widgetId == $paWidgetId) {
                $slots = $value['slots'];
            }
        }
        
        $productIds = [];
        foreach($slots as $key=>$value) {
            //$this->logger->info("key: " . $key . ", productId: " . $value['products'][0]['refId']); 
            $productId = $value['products'][0]['refId'];
            array_push($productIds, $productId);
        }
        
        //$recommendationsCollection = $this->productCollectionFactory->getIdFilter($productIds);
        $recommendationsCollection = $this->productCollectionFactory->create();
        $recommendationsCollection->addAttributeToSelect('*');
        $recommendationsCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
        $recommendationsCollection->addMinimalPrice()->addFinalPrice();
        
        //$this->logger->info("Response: " . $webSignUpResult); 
        return $recommendationsCollection;
    }
    
    public function getProductPrice($product){
        return $this->listProductBlock->getProductPrice($product);
    }
    
    public function getAddToCartPostParams($product){
        return $this->listProductBlock->getAddToCartPostParams($product);
    }
    
}