<?php

namespace Digidirect\ParticularAudienceAPI\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * CheckoutCartAddObserver
 */
class CheckoutCartAddObserver implements ObserverInterface {

    protected $_layout;

    protected $_storeManager;

    protected $_request;

    protected $checkoutSession;

    protected $logger;

    /**
     * __construct
     *
     * @param \Magento\Store\Model\StoreManagerInterface storeManager
     * @param \Magento\Framework\View\LayoutInterface layout
     * @param \Magento\Framework\App\RequestInterface request
     * @param \Magento\Framework\Serialize\SerializerInterface serializer
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_layout = $layout;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->serializer = $serializer;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * execute
     *
     * @param EventObserver observer
     *
     * @return void
     */
    public function execute(EventObserver $observer) {

        $postValue = $this->_request->getParams();
        $item = $observer->getQuoteItem();

        $customOptions = [];

        $refId = ['label' => 'refId', 'value' => $item->getProductId()];
        array_push($customOptions, $refId);

        if (isset($postValue['route_id']) && $postValue['route_id']) {
            $routeId = [];
            $routeId = ['label' => 'routeId', 'value' => $postValue['route_id']];
            array_push($customOptions, $routeId);
        }
        if (isset($postValue['widget_id']) && $postValue['widget_id']) {
            $widgetId = [];
            $widgetId = ['label' => 'widgetId', 'value' => $postValue['widget_id']];
            array_push($customOptions, $widgetId);
        }
        if (isset($postValue['recommender_id']) && $postValue['recommender_id']) {
            $recommenderId = [];
            $recommenderId = ['label' => 'recommenderId', 'value' => $postValue['recommender_id']];
            array_push($customOptions, $recommenderId);
        }
        if (isset($postValue['campaign_id']) && $postValue['campaign_id']) {
            $campaignId = [];
            $campaignId = ['label' => 'campaignId', 'value' => $postValue['campaign_id']];
            array_push($customOptions, $campaignId);
        }
        if (isset($postValue['tactic_id']) && $postValue['tactic_id']) {
            $tacticId = [];
            $tacticId = ['label' => 'tacticId', 'value' => $postValue['tactic_id']];
            array_push($customOptions, $tacticId);
        }
        if (isset($postValue['retail_boost_collection_campaign_id']) && $postValue['retail_boost_collection_campaign_id']) {
            $retailBoostCollectionCampaignId = [];
            $retailBoostCollectionCampaignId = ['label' => 'retailBoostCollectionCampaignId', 'value' => $postValue['retail_boost_collection_campaign_id']];
            array_push($customOptions, $retailBoostCollectionCampaignId);
        }
        if (isset($postValue['adset_id']) && $postValue['adset_id']) {
            $adSetId = [];
            $adSetId = ['label' => 'adSetId', 'value' => $postValue['adset_id']];
            array_push($customOptions, $adSetId);
        }
        if (isset($postValue['adset_version']) && $postValue['adset_version']) {
            $adSetVersion = [];
            $adSetVersion = ['label' => 'adSetVersion', 'value' => $postValue['adset_version']];
            array_push($customOptions, $adSetVersion);
        }
        if (isset($postValue['cost_per_click']) && $postValue['cost_per_click']) {
            $costPerClick = [];
            $costPerClick = ['label' => 'costPerClick', 'value' => $postValue['cost_per_click']];
            array_push($customOptions, $costPerClick);
        }
        if (isset($postValue['timestamp']) && $postValue['timestamp']) {
            $timeStamp = [];
            $timeStamp = ['label' => 'timeStamp', 'value' => $postValue['timestamp']];
            array_push($customOptions, $timeStamp);
        }
        if (isset($postValue['hmac_salt']) && $postValue['hmac_salt']) {
            $hmacSalt = [];
            $hmacSalt = ['label' => 'hmacSalt', 'value' => $postValue['hmac_salt']];
            array_push($customOptions, $hmacSalt);
        }
        if (isset($postValue['hmac']) && $postValue['hmac']) {
            $hmac = [];
            $hmac = ['label' => 'hmac', 'value' => $postValue['hmac']];
            array_push($customOptions, $hmac);
        }
        if (isset($postValue['keyword_id']) && $postValue['keyword_id']) {
            $keywordId = [];
            $keywordId = ['label' => 'keywordId', 'value' => $postValue['keyword_id']];
            array_push($customOptions, $keywordId);
        }

        $productExist = $this->checkoutSession->getQuote()->hasProductId($item->getProductId());

        $item->addOption([
            'product_id' => $item->getProductId(),
            'code' => 'additional_options',
            'value' => $this->serializer->serialize($customOptions),
        ]);

        //if (!$productExist) {
        //}

    }
}
