<?php

namespace Digidirect\ParticularAudienceAPI\Block\Widget\Homepage;


class TrendingDigiMarketProducts extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $productCollectionFactory;

    protected $variable;

    protected $curl;

    protected $jsonSerializer;

    protected $logger;

    protected $cookieManager;

    protected $cookieMetadataFactory;

    protected $_template = 'Digidirect_ParticularAudienceAPI::widget/product-widget.phtml';

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Variable\Model\Variable $variable,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Catalog\Block\Product\ListProduct $listProductBlock,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->variable = $variable;
        $this->curl = $curl;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->listProductBlock = $listProductBlock;
        parent::__construct($context, $data);
    }

    public function getWidgetTitle() {
        return 'Trending digiMarket Products';
    }

    public function getWidgetClass() {
        return 'trending-digimarket-products-widget';
    }

    public function getViewAllLink() {
        return false;
    }

    public function getRecommendedProducts(){

        //Get token from custom variable
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');
        $paWidgetId = '886fc4e3-a9f0-ee11-abf3-02bf4bf6447c';

        $customerId = $this->cookieManager->getCookie('pa_customer_id');
        //$this->logger->info("customerId: " . $customerId);
        if ($customerId) {
            $customerIdParam = "&customerId=".$customerId;
        } else {
            $customerIdParam = "";
        }

        $getRecommendationsUrl = "https://api-recs.particularaudience.com/3.0/recommendations?currentUrl=https://www.digidirect.com.au/pa-digi-home-page&expandProductDetails=false".$customerIdParam;
        //$this->logger->info("getRecommendationsUrl: " . $getRecommendationsUrl);

        $this->curl->addHeader("Content-Type", "application/json");
        $this->curl->addHeader("Authorization", "Bearer " . $bearerToken);
        $this->curl->get($getRecommendationsUrl);

        $getRecommendationsResult = $this->curl->getBody();
        $getRecommendationsResultJson = $this->jsonSerializer->unserialize($getRecommendationsResult);

        //$slots = $getRecommendationsResultJson['recommendations']['route']['widgets'][0]['slots'];
        $widgets = $getRecommendationsResultJson['recommendations']['route']['widgets'];

        foreach($widgets as $key=>$value) {
            $widgetId = $value['id'];
            if ($widgetId == $paWidgetId) {
                $slots = $value['slots'];
            }
        }

        if (isset($slots)) {
            $productIds = [];
            foreach($slots as $key=>$value) {
                $productId = $value['products'][0]['refId'];
                array_push($productIds, $productId);
            }
            $recommendationsCollection = $this->productCollectionFactory->create();
            $recommendationsCollection->addAttributeToSelect('*');
            $recommendationsCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $recommendationsCollection->getSelect()->orderRand();
        } else {

            $recommendationsCollection = $this->productCollectionFactory->create();
            $recommendationsCollection->addAttributeToSelect('*');
            $recommendationsCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $recommendationsCollection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
            $recommendationsCollection->addAttributeToFilter("marketplacer_seller", array("neq" => 20329));
            $recommendationsCollection->addAttributeToFilter("marketplacer_seller", array("notnull" => true));
            $recommendationsCollection->getSelect()->limit(10);
        }

        return $recommendationsCollection;
    }

    public function getProductPrice($product){
        return $this->listProductBlock->getProductPrice($product);
    }

    public function getAddToCartPostParams($product){
        return $this->listProductBlock->getAddToCartPostParams($product);
    }

}
