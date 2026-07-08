<?php

namespace Digidirect\Collect\Model\Plugin\Checkout;

use Digidirect\Collect\Helper\Config\Address as AddressCollectHelper;
use Digidirect\Collect\Helper\Data as CollectHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class DefaultConfigProvider
{
    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * StorageHandler
     *
     * @var \Digidirect\Collect\Model\StorageHandler
     */
    protected $storageHandler;

    /**
     * @var CollectHelper
     */
    protected $collectHelper;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Digidirect\Collect\Helper\Config\Address
     */
    protected $addressCollectHelper;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|null
     */
    protected $eventManager;
    
    protected $_cart;
    
    protected $_product;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Digidirect\Collect\Model\StorageHandler $storageHandler
     * @param CollectHelper $collectHelper
     * @param \Digidirect\Collect\Helper\Config\Address $addressCollectHelper
     * @param \Magento\Framework\Event\ManagerInterface|null $eventManager
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Digidirect\Collect\Model\StorageHandler $storageHandler,
        CollectHelper $collectHelper,
        AddressCollectHelper $addressCollectHelper,
        EventManagerInterface $eventManager = null,
        GetSourceItemsBySku $getSourceItemsBySku,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->storageHandler = $storageHandler;
        $this->collectHelper = $collectHelper;
        $this->addressCollectHelper = $addressCollectHelper;
        $this->eventManager = $eventManager ?: ObjectManager::getInstance()->get(EventManagerInterface::class);
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->_cart = $cart;
        $this->_product = $product;
    }

    /**
     * AfterGetConfig
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param [] $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        if ($this->collectHelper->isCollectEnable()) {
            if (isset($result['quoteData'])) {
                $collectItems = true;
                $quoteId = $this->checkoutSession->getQuote()->getId();

                $result['quoteData']['collect_items'] = $this->collectHelper->isCollectItems($quoteId);
                $result['quoteData']['delivery_items'] = $this->collectHelper->isDeliveryItems($quoteId);

                $quote = $this->checkoutSession->getQuote();

                if (empty($result['paymentMethods'])) {
                    $paymentMethods = [];
                    if ($quote->getIsVirtual() || $collectItems) {
                        foreach ($this->paymentMethodManagement->getList($quote->getId()) as $paymentMethod) {
                            $paymentMethods[] = [
                                'code' => $paymentMethod->getCode(),
                                'title' => $paymentMethod->getTitle()
                            ];
                        }
                    }
                    $result['paymentMethods'] = $paymentMethods;
                }
            }
            
            $items = $this->_cart->getQuote()->getAllItems();
            
            $totalqty = 1;
            foreach ($items as $item) {
                $prodId = $item->getProductId();
                $product = $this->_product->load($prodId);

                $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());
                $qty = 0;
                foreach ($sourceItems as $sourceItemId => $sourceItem) {
                    //comment out clint Apr 3, 2025
                    $store = $sourceItem->getSourceCode();
                    if ($store != "default" && $store != "3WHS" && $store != "SWHS") {
                        if ($sourceItem->getQuantity() < 0) {
                           $sourceQty = 0;
                        } else {
                           $sourceQty = $sourceItem->getQuantity(); 
                        }
                        $qty = $qty + $sourceQty;
                    }
                }
                $totalqty = $totalqty * $qty;
            }
            
            if ($totalqty > 0) {
                $result['quoteData']['products_available_in_any_store'] = true;
            } else {
                $result['quoteData']['products_available_in_any_store'] = false;
            }
            
            $result['quoteData']['collect_places'] = $this->getCollectPlaceInformation();
            $singleVariation = $this->collectHelper->isSingleVariation();
            $singleCartVariation = $this->collectHelper->isSingleCartVariation();
            $result['quoteData']['is_single_collect_variation'] = $singleVariation;
            $result['quoteData']['is_single_cart_collect_variation'] = $singleCartVariation;
            $result['quoteData']['is_collect_enable_on_checkout'] = $this->collectHelper->isCollectEnableOnCheckout();
            $result['quoteData']['collect_default_address'] = $singleVariation ? $this->getCollectDefaultAddress() : [];
            $result['quoteData']['deliver_instead_url'] = $this->collectHelper->getDeliverInsteadUrl();
            $result['quoteData']['change_place_url'] = $this->collectHelper->getChangePlaceUrl();
            $result['quoteData']['get_places_url'] = $this->collectHelper->getPlacesUrl();
            $result['quoteData']['distance_list'] = explode(',', $this->collectHelper->getDefaultDistanceRange());
        }

        return $result;
    }

    /**
     * Get collect place information
     *
     * @return []
     * @throws \Exception
     */
    protected function getCollectPlaceInformation()
    {
        $collectPlaces = [];
        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();

        if (empty($quoteItems)) {
            return [];
        }

        $findPlaces = [];
        foreach ($quoteItems as $quoteItem) {
            if (!$quoteItem->getCollectPlaceId() || !$quoteItem->getCollectPlaceStorageName()) {
                continue;
            }

            /** @var \Magento\Quote\Model\Quote\Item $quoteItem * */
            $key = implode('__', [$quoteItem->getCollectPlaceId(), $quoteItem->getCollectPlaceStorageName()]);
            if (!isset($findPlaces[$key])) {
                $findPlaces[$key]['collect_place'] = $this->storageHandler->getCollectPlaceById(
                    $quoteItem->getCollectPlaceId(),
                    $quoteItem->getCollectPlaceStorageName()
                );
            }
            /** @var $collectPlace \Digidirect\Collect\Api\Data\CollectPlaceInterface */
            $collectPlace = $findPlaces[$key]['collect_place'];
            if ($collectPlace instanceof \Digidirect\Collect\Api\Data\CollectPlaceInterface) {
                $collectPlaceObject = new DataObject([
                    'item_id' => $quoteItem->getItemId(),
                    'item_name' => $quoteItem->getName(),
                    'collect_place_name' => $collectPlace->getName(),
                    'collect_place_address' => $collectPlace->getAddress()
                ]);
                $this->eventManager->dispatch(
                    'digidirect_collect_checkout_config_collect_place_information',
                    [
                        'quote_item' => $quoteItem,
                        'collect_place' => $collectPlace,
                        'info' => $collectPlaceObject
                    ]
                );
                $collectPlaces[$quoteItem->getItemId()] = $collectPlaceObject->getData();
            }
        }

        return $collectPlaces;
    }

    /**
     * Get default collect address
     *
     * @return string[]
     */
    protected function getCollectDefaultAddress()
    {
        $addresses = $this->addressCollectHelper->getDefaultAddressArray();

        return $addresses;
    }
    
    public function checkIfCanningtonOnly() 
    {
        $quoteItems = $this->checkoutSession->getQuote()->getAllVisibleItems();
        $skuQty = $this->collectHelper->getSkuToQtyByItems($quoteItems);

        $cartItems = $this->_cart->getQuote()->getAllItems();
        
        $stores = [];

        $sydnQty = 1;
        $bondQty = 1;
        $melbQty = 1;
        $brisQty = 1;
        $miraQty = 1;
        $cannQty = 1;
        $parrQty = 1;
        $stPetersQty = 1;

        $totalCann = 0.0;
        $totalQtyOnOtherSources = 0;

        foreach ($cartItems as $cartItem) {

            $prodId = $cartItem->getProductId();
            $product = $this->_product->load($prodId);

            $sourceItems = $this->getSourceItemsBySku->execute($product->getSku());

            foreach ($sourceItems as $sourceItemId => $sourceItem) {
                
                $getQty = $sourceItem->getQuantity();
                $store = $sourceItem->getSourceCode();

                if ((!in_array($store, $stores)))  {
                    array_push($stores, $store);
                }

                if ($sourceItem->getSourceCode() == 'SYDN') {
                    $sydnQty = $sydnQty * $getQty;
                    $totalQtyOnOtherSources += $sydnQty;
                } elseif ($sourceItem->getSourceCode() == 'BOND') {
                    $bondQty = $bondQty * $getQty;
                    $totalQtyOnOtherSources += $bondQty;
                } elseif ($sourceItem->getSourceCode() == 'MELB') {
                    $melbQty = $melbQty * $getQty;
                    $totalQtyOnOtherSources += $melbQty;
                } elseif ($sourceItem->getSourceCode() == 'BRIS') {
                    $brisQty = $brisQty * $getQty;
                    $totalQtyOnOtherSources += $brisQty;
                } elseif ($sourceItem->getSourceCode() == 'MIRA') {
                    $miraQty = $miraQty * $getQty;
                    $totalQtyOnOtherSources += $miraQty;
                } elseif ($sourceItem->getSourceCode() == 'PARR') {
                    $parrQty = $parrQty * $getQty;
                    $totalQtyOnOtherSources += $parrQty;
                } elseif ($sourceItem->getSourceCode() == 'CANN') {

                    $wiserPrice = $product->getWiserPrice();
                    $finalPrice = $product->getFinalPrice();

                    $lastPrice = $finalPrice;

                    if ($wiserPrice > 0 && $wiserPrice < $finalPrice) {
                        $lastPrice = $wiserPrice;
                    }

                    $totalCann += $lastPrice;
                    $cannQty = $cannQty * $getQty;

                }
            }
        }
        
        $canningtonOnly = false;
        /*$this->logger->info('$totalCann: ' . $totalCann);
        $this->logger->info('$cannQty: ' . $cannQty);
        $this->logger->info('$totalQtyOnOtherSources: ' . $totalQtyOnOtherSources);*/
        
        if ($totalCann < 1000 && $cannQty > 0 && $totalQtyOnOtherSources < 1) {
             $canningtonOnly = true;   
        }
        
        return $canningtonOnly;
    }
}
