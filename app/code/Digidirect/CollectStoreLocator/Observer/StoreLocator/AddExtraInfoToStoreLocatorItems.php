<?php
namespace Digidirect\CollectStoreLocator\Observer\StoreLocator;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Digidirect\CollectAbstractEntity\Helper\Places;
use Digidirect\Collect\Helper\Data;
use Digidirect\Collect\Api\CollectPlaceRepositoryInterface;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku;

/**
 * Class AddExtraInfoToStoreLocatorItems
 * @package Digidirect\CollectStoreLocator\Observer\StoreLocator
 */
class AddExtraInfoToStoreLocatorItems implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Places
     */
    protected $placesHelper;

    /**
     * @var Data
     */
    protected $collectHelper;
    
    protected $logger;
    
    protected $_cart;
    
    protected $_product;

    /**
     * AddExtraInfoToStoreLocatorItems constructor.
     * @param Session $checkoutSession
     * @param Places $placesHelper
     * @param Data $collectHelper
     */
    public function __construct(
        Session $checkoutSession,
        Places $placesHelper,
        Data $collectHelper,
        GetSourceItemsBySku $getSourceItemsBySku,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->placesHelper = $placesHelper;
        $this->collectHelper = $collectHelper;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->logger = $logger;
        $this->_cart = $cart;
        $this->_product = $product;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $transportObject = $observer->getTransportObject();
        $locatorStores = $transportObject->getData('items');
        $locatorStores = $this->addAvailabilityInfoToItems($locatorStores);
        $transportObject->setData(['items' => $locatorStores]);
        echo $this->console_log($locatorStores);
    }

    /**
     * @param array $items
     * @return array
     */
    public function addAvailabilityInfoToItems($items)
    {
        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        $skuQty = $this->collectHelper->getSkuToQtyByItems($quoteItems);
        $places = $this->placesHelper->getAllCollectPlacesEntities($skuQty);

        $cartItems = $this->_cart->getQuote()->getAllItems();
        
        $stores = [];
            
        $totalCann = 0.0;
        $totalQtyOnOtherSources = 0;
        
        foreach ($items as $key => $storeData) {

            $id = $storeData['entity_id'];
            $qty = 0;
            //$items[$key]['click_and_collect'] = true;

            if (empty($places[$id])) {
                continue;
            }

            //$place = $places[$id];
            //if ($place->hasData(CollectPlaceRepositoryInterface::KEY_IS_UNAVAILABLE)) {
                $items[$key]['available'] = true; // Andrew requested that all store is selectable; !$place->getData(CollectPlaceRepositoryInterface::KEY_IS_UNAVAILABLE);
            //}

            $sydnQty = 1;
            $bondQty = 1;
            $melbQty = 1;
            $brisQty = 1;
            $miraQty = 1;
            $cannQty = 1;
            $parrQty = 1;
            $stPetersQty = 1;
            $strathfieldQty = 1;
          
            foreach ($cartItems as $cartItem) {

                $prodId = $cartItem->getProductId();
                $product = $this->_product->load($prodId);

                $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());

                foreach ($sourceItems as $sourceItemId => $sourceItem) {
                    //echo $this->console_log($sourceItem->getQuantity());
                    //echo $this->console_log($sourceItem->getSourceCode());
                    //$qty .= $sourceItem->getQuantity();
                    //$this->logger->info('getSourceCode:' . $sourceItem->getSourceCode() . ', getQuantity:' . $sourceItem->getQuantity());

                    $getQty = $sourceItem->getQuantity();
                    $store = $sourceItem->getSourceCode();
                    
                    if ((!in_array($store, $stores)))  {
                        array_push($stores, $store);
                    }

                    if ($id == 1 && $sourceItem->getSourceCode() == 'SYDN') {
                        $sydnQty = $sydnQty * $getQty;
                        $totalQtyOnOtherSources += $sydnQty;
                    } elseif ($id == 31 && $sourceItem->getSourceCode() == 'BOND') {
                        $bondQty = $bondQty * $getQty;
                        $totalQtyOnOtherSources += $bondQty;
                    } elseif ($id == 7 && $sourceItem->getSourceCode() == 'MELB') {
                        $melbQty = $melbQty * $getQty;
                        $totalQtyOnOtherSources += $melbQty;
                    } elseif ($id == 10 && $sourceItem->getSourceCode() == 'BRIS') {
                        $brisQty = $brisQty * $getQty;
                        $totalQtyOnOtherSources += $brisQty;
                    } elseif ($id == 13 && $sourceItem->getSourceCode() == 'MIRA') {
                        $miraQty = $miraQty * $getQty;
                        $totalQtyOnOtherSources += $miraQty;
                    } elseif ($id == 32 && $sourceItem->getSourceCode() == 'PARR') {
                        $parrQty = $parrQty * $getQty;
                        $totalQtyOnOtherSources += $parrQty;
                    } elseif ($id == 16 && $sourceItem->getSourceCode() == 'CANN') {
                        
                        $wiserPrice = $product->getWiserPrice();
                        $finalPrice = $product->getFinalPrice();
                        
                        $lastPrice = $finalPrice;
                        
                        if ($wiserPrice < $finalPrice) {
                            $lastPrice = $wiserPrice;
                        }
                        
                        $totalCann += $lastPrice;
                        $cannQty = $cannQty * $getQty;
                        
                        $this->logger->info('$wiserPrice, ' . $wiserPrice);
                        $this->logger->info('$finalPrice, ' . $finalPrice);
                        $this->logger->info('$totalCann, ' . $totalCann);
                        
                    }
                }
            }
            
            if ($totalCann < 1000 && $cannQty > 0 && $totalQtyOnOtherSources < 1) {
                if ($id == 16) { //CANN
                    $items[$key]['click_and_collect'] = true;
                } else {
                    $items[$key]['click_and_collect'] = NULL;
                }
            } else {
                if (is_null($id)) {
                    $items[$key]['click_and_collect'] = false;
                } else {
                    if ($id == 1 && $sydnQty > 0) {
                        if (in_array('SYDN', $stores)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            $items[$key]['click_and_collect'] = false;
                        }
                    } elseif ($id == 31 && $bondQty > 0) {
                        if (in_array('BOND', $stores)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            $items[$key]['click_and_collect'] = false;
                        }
                    } elseif ($id == 7 && $melbQty > 0) {
                        if (in_array('MELB', $stores)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            $items[$key]['click_and_collect'] = false;
                        }
                    } elseif ($id == 10 && $brisQty > 0) {
                        if (in_array('BRIS', $stores)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            $items[$key]['click_and_collect'] = false;
                        }
                    } elseif ($id == 13 && $miraQty > 0) {
                        if (in_array('MIRA', $stores)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            $items[$key]['click_and_collect'] = false;
                        }
                    } elseif ($id == 16) {
                        if (in_array('CANN', $stores) && ($cannQty > 0)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            if ($totalCann < 1000) {
                                $items[$key]['click_and_collect'] = NULL;
                            } else {
                                $items[$key]['click_and_collect'] = false;
                            }
                        }
                    } elseif ($id == 35) { //SWHS
                        
                        $items[$key]['click_and_collect'] = NULL;
                        
                    } elseif ($id == 32 && $parrQty > 0) {
                        if (in_array('PARR', $stores)) {
                            $items[$key]['click_and_collect'] = true;
                        } else {
                            $items[$key]['click_and_collect'] = false;
                        }
                    } else {
                        $items[$key]['click_and_collect'] = false;
                    }
                }
            } 
        }
        
        $this->logger->info('$totalCann: ' . $totalCann);
        $this->logger->info('$cannQty: ' . $cannQty);
        $this->logger->info('$totalQtyOnOtherSources: ' . $totalQtyOnOtherSources);

        return $items;
    }

    function console_log($output, $with_script_tags = true) {
        $js_code = 'console.log(' . json_encode($output, JSON_HEX_TAG) .
            ');';
        if ($with_script_tags) {
            $js_code = '<script>' . $js_code . '</script>';
        }
        echo $js_code;
    }
}
