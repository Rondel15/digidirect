<?php

namespace Digidirect\ParticularAudienceAPI\Block\Widget\Homepage;


class BlueRightSide extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected $productCollectionFactory;

    protected $variable;

    protected $curl;

    protected $jsonSerializer;

    protected $logger;

    protected $cookieManager;

    protected $cookieMetadataFactory;

    protected $_template = 'Digidirect_ParticularAudienceAPI::widget/blue-right-side.phtml';


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

    public function getProductPrice($product){
        return $this->listProductBlock->getProductPrice($product);
    }

    public function getCookieValue(){
        $customerId = $this->cookieManager->getCookie('pa_customer_id');
        return $this->console_log("getCookie('pa_customer_id')");
    }

    public function getRecommendedProducts(){

        //Get token from custom variable
        $variableData = $this->variable->loadByCode('pa_bearer_token');
        $bearerToken = $variableData->getValue('text');
        $paWidgetId = '458d80ed-033e-ec11-aae9-02dca44cceec';

        $customerId = $this->cookieManager->getCookie('pa_customer_id');
        $this->logger->info("PAC: " . $this->console_log("getCookie('pa_customer_id')"));
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
                //$this->logger->info("productId: " . $productId);

                array_push($productIds, $productId);
            }
            $recommendationsCollection = $this->productCollectionFactory->create();
            $recommendationsCollection->addAttributeToSelect('*');
            $recommendationsCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $recommendationsCollection->getSelect()->orderRand();
        } else {
            $productIds = [62517,39328,62727,141171,12875];
            $recommendationsCollection = $this->productCollectionFactory->create();
            $recommendationsCollection->addAttributeToSelect('*');

            $recommendationsCollection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
            $recommendationsCollection->addAttributeToFilter('status', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

            $recommendationsCollection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $recommendationsCollection->getSelect()->limit(10);
        }

        return $recommendationsCollection;
    }

    public function console_log($output) {
        echo "<script>".json_encode($output)."</script>";
    }
}
