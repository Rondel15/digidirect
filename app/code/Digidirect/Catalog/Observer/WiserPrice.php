<?php

namespace Digidirect\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Digidirect\FreeGift\Model\Cart\Item as CartItem;

class WiserPrice implements ObserverInterface
{
    protected $customer;

    protected $logger;

    protected $_productOptions;

    protected $_productRepositoryInterface;

    protected $_productRepository;

    protected $_giftItem;
    
    protected $_request;

    protected $serializer;

    protected $checkoutSession;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Product\Option $productOptions,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\Product $productRepository,
        CartItem $giftItem,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customer = $customerSession;
        $this->_productOptions = $productOptions;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_productRepository = $productRepository;
        $this->_giftItem = $giftItem;
        $this->_request = $request;
        $this->serializer = $serializer;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {

        $postValue = $this->_request->getParams();
        //get the item just added to cart
        $item = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product');
        $sku = $product->getData('sku');

        $discount2 = []; //[122428,124928,130029,133828,135538,135790,137132,137431,139609,139908,142338,144625,144626,146718,146719,146818,146876,147859,148028,148817,149367,149381,152803,153589,153590,154948,154953,155161,155162,155166,155212,155520,155973];
        $discount5 = []; //[137376,137952,139517,141539,142768,149131,149132,149133,149950,153379,153380,153381,155158,155159,156474,156475,156476,156477,117561,117562,134146,117492,117494,122574,149496,149497];
        $discount10 = []; //[154979,154784,154782,154783,154785,151173,151169,141197,136339,148815,148728,155243,155244];
        $discount15 = []; //[133160,147857,130998,117586,153643,153642];

        $isDigiClub = 0;

        if ($this->customer->isLoggedIn()) {
            $customerGroupId = $this->customer->getCustomer()->getGroupId();
            if ($customerGroupId == 10) {
                $isDigiClub = 1;
            }
        }

        //(optional) get the parent item, if exists
        $item = ($item->getParentItem() ? $item->getParentItem() : $item);

        $price = $product->getData('final_price');//$product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $wiserPrice = $product->getData('wiser_price');
        $basePrice = $product->getPrice();
        $discountWiserPrice = round($basePrice - $wiserPrice, 2);

        $isDigiPrint = $product->getData('is_digiprint');

        $this->_productRepositoryInterface->getById($product->getId());
        $this->_productRepository->load($product->getId());

        $finalPrice = $price;

        $finalProductPrice = $finalPrice;

        //$this->logger->info('$basePrice: ' . $basePrice . ', $finalPrice: ' . $finalPrice .', $wiserPrice: ' . $wiserPrice);
        if ($this->_giftItem->isFreeGiftItem($item)) {
            $finalProductPrice = 0;
        } else {
            if ($wiserPrice == 0 || empty($wiserPrice)) {
                $finalProductPrice = $finalPrice;
            } else {
                $digiProtectPrice = 0;

                $selectedOption = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                //$this->logger->info('$selectedOption: ' . json_encode($selectedOption));

                $customOptions = $this->_productOptions->getProductOptionCollection($product);
                foreach($customOptions as $optionKey => $optionVal) {
                    foreach($optionVal->getValues() as $valuesKey => $valuesVal) {
                        //$this->logger->info('$valuesVal: ' . $valuesVal->getTitle(). ' ' .$valuesVal->getPrice());
                        if (isset($selectedOption['options'])) {
                            $digiProtectPrice = $valuesVal->getPrice();
                        }
                    }
                }

                $wiserPlusDigiProtect = $wiserPrice + $digiProtectPrice;

                if ($finalPrice > $wiserPlusDigiProtect) {
                    if ($wiserPrice > 0 && !empty($wiserPrice)) {
                        if ($wiserPrice < $price) {
                            if (!$isDigiPrint) {
                                if ((in_array($sku, $discount2)) && $isDigiClub) {
                                    $wiserPrice = $wiserPrice - ($wiserPrice * 0.02);
                                } elseif ((in_array($sku, $discount5)) && $isDigiClub) {
                                    $wiserPrice = $wiserPrice - ($wiserPrice * 0.05);
                                } elseif ((in_array($sku, $discount10)) && $isDigiClub) {
                                    $wiserPrice = $wiserPrice - ($wiserPrice * 0.10);
                                } elseif ((in_array($sku, $discount15)) && $isDigiClub) {
                                    $wiserPrice = $wiserPrice - ($wiserPrice * 0.15);
                                }
                                $finalPrice = $wiserPrice;
                            }
                        } else {
                            $finalPrice = $price;
                        }
                    } else {
                        $finalPrice = $price;
                    }
                    $finalProductPrice = $finalPrice + $digiProtectPrice;

                } else {

                    $finalProductPrice = $finalPrice;
                }
            }
        }

        $item->setCustomPrice($finalProductPrice);
        $item->setOriginalCustomPrice($finalProductPrice);
        $item->getProduct()->setIsSuperMode(true);
        
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
    }
}
