<?php

namespace Digidirect\Collect\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Digidirect\Collect\Helper
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DELIVERY_TYPE_COLLECT = 'collect';
    const DELIVERY_TYPE_DELIVER = 'delivery';

    const VARIATION_TYPE_FULL = 'full';
    const VARIATION_TYPE_SINGLE = 'single';
    const VARIATION_TYPE_CART_SINGLE = 'single_cart';

    const STORE_NAME_SEARCH_CONDITION = '{STORE_NAME}';

    const XML_COLLECT_METHOD_ENABLE = 'carriers/collect/active';
    const XML_ENABLE_SINGLE_STORE_IN_CART_RESTRICTION = 'carriers/collect/enable_single_store_in_cart_restriction';
    const XML_COLLECT_METHOD_ENABLE_ON_PDP = 'carriers/collect/active_on_pdp';
    const XML_COLLECT_METHOD_ENABLE_ON_CART = 'carriers/collect/active_on_cart';
    const XML_COLLECT_METHOD_ENABLE_ON_CHECKOUT = 'carriers/collect/active_on_checkout';
    const XML_COLLECT_METHOD_TITLE = 'carriers/collect/title';
    const XML_COLLECT_METHOD_NAME = 'carriers/collect/name';

    const XML_COLLECT_VARIATION = 'carriers/collect/variation';
    const XML_COLLECT_STOCK_UPDATE_INTERFACE = 'carriers/collect/stock_update_interface';
    const XML_DEFAULT_DISTANCE_RANGE = 'carriers/collect/default_distance_range';

    const ADD_TO_CART_VALIDATION_FAILED_FLAG = 'digidirect_collect_add_to_cart_validation_failed';

    const GEO_LOCATION_METHOD_GOOGLE_API = 'google_api';
    const GEO_LOCATION_METHOD_POST_CODE = 'post_code';

    const XML_GEO_LOCATION_METHOD = 'carriers/collect/geo_location_method';

    const XML_PRODUCT_AVAILABLE_MESSAGE = 'carriers/collect/message_product_is_available_in_previously_store';
    const XML_PRODUCT_NOT_AVAILABLE_MESSAGE = 'carriers/collect/message_product_is_not_available_in_previously_store';

    const CONFIG_SHOW_UNAVAILABLE_PLACES = 'carriers/collect/show_unavailable_places';
    const CONFIG_PLACES_ON_PAGE = 'carriers/collect/places_on_page';

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * CartItemRepository
     *
     * @var \Magento\Quote\Api\CartItemRepositoryInterface
     */
    protected $_cartItemRepository;

    /**
     * CheckoutSession
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * StorageHandler
     *
     * @var \Digidirect\Collect\Model\StorageHandler
     */
    protected $_storageHandler;

    /**
     * CollectQuantityValidator
     *
     * @var \Digidirect\Collect\Model\CollectQuantityValidator
     */
    protected $_collectQuantityValidator;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_state;

    /**
     * @var bool[]
     */
    protected $_existingItems = [];

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Digidirect\Collect\Model\StorageHandler $storageHandler
     * @param \Digidirect\Collect\Model\CollectQuantityValidator $collectQuantityValidator
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartItemRepositoryInterface $cartItemRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Digidirect\Collect\Model\StorageHandler $storageHandler,
        \Digidirect\Collect\Model\CollectQuantityValidator $collectQuantityValidator,
        \Magento\Framework\App\State $state
    ) {
        parent::__construct($context);

        $this->_quoteRepository = $quoteRepository;
        $this->_cartItemRepository = $cartItemRepository;
        $this->_checkoutSession = $checkoutSession;
        $this->_storageHandler = $storageHandler;
        $this->_collectQuantityValidator = $collectQuantityValidator;
        $this->_state = $state;
    }

    /**
     * Change Deliver Method in quote
     *
     * @param [] $params
     * @param string $method
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function changeQuoteDeliverMethod($params, $method = self::DELIVERY_TYPE_COLLECT)
    {
        if ($method != self::DELIVERY_TYPE_DELIVER
            && (empty($params['collect_place_id']) || empty($params['collect_place_storage_name']))) {
            return false;
        }

        $quoteItemIds = [];
        if (!empty($params['quote_item_id'])) {
            $quoteItemIds[] = $params['quote_item_id'];
        } else {// change for all quote items
            $quoteItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($quoteItems as $quoteItem) {
                $quoteItemIds[] = $quoteItem->getId();
            }
        }
        foreach ($quoteItemIds as $quoteItemId) {
            $params['quote_item_id'] = $quoteItemId;
            $result = $this->changeDeliverMethod($params, $method);
            if ($result['result'] !== true) {
                //break id we can`t change for same item
                return $result;
            }
        }

        $collectPlace = $this->_storageHandler->getCollectPlaceById(
            $params['collect_place_id'],
            $params['collect_place_storage_name']
        );

        if ($collectPlace instanceof \Digidirect\Collect\Api\Data\CollectPlaceInterface) {
            return [
                'collect_place_name' => $collectPlace->getName(),
                'collect_place_id' => $collectPlace->getId(),
                'collect_place_address' => $collectPlace->getAddress(),
                'result' => true
            ];
        }

        return ['result' => true];
    }

    /**
     * Change Deliver Method in quote item
     *
     * @param [] $params
     * @param string $method
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function changeDeliverMethod($params, $method = self::DELIVERY_TYPE_COLLECT)
    {
        if (($method == self::DELIVERY_TYPE_DELIVER && empty($params['quote_item_id']))
            || ($method == self::DELIVERY_TYPE_COLLECT && empty($params['quote_item_id'])
                && empty($params['collect_place_id']))
        ) {
            return false;
        }

        $quoteItemId = $params['quote_item_id'];
        $collectPlaceId = $params['collect_place_id'];
        $collectPlaceStorageName = $params['collect_place_storage_name'];
        $result = [];
        /** @var $quoteItem \Magento\Quote\Model\Quote\Item */
        $quoteItem = $this->_checkoutSession->getQuote()->getItemById($quoteItemId);
        $qtyCheck = null;
        if ($quoteItem) {
            if ($method == self::DELIVERY_TYPE_COLLECT && $collectPlaceId && $collectPlaceStorageName) {
                if ($this->_collectQuantityValidator->checkProductQtyInSource($quoteItem, $collectPlaceId)) {
                    $qtyCheck = true;
                    $quoteItem->setCollectPlaceId($collectPlaceId);
                    $quoteItem->setCollectPlaceStorageName($collectPlaceStorageName);
                } else {
                    $qtyCheck = false;
                    $quoteItem->setCollectPlaceId(null);
                    $quoteItem->setCollectPlaceStorageName(null);
                }
            } else {
                $quoteItem->setCollectPlaceId(null);
                $quoteItem->setCollectPlaceStorageName(null);
            }

            try {
                $this->saveQuoteItem($quoteItem);
                $this->_checkoutSession->getQuote()->collectTotals();
                $this->_checkoutSession->getQuote()->save();
                $result = ['result' => true];
            } catch (LocalizedException $e) {
                $this->logError($e->getMessage());

                return ['result' => false, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $this->logError($e->getMessage());

                return ['result' => false, 'message' => $this->getExceptionMessage($e)];
            }

            if ($qtyCheck === false) {
                return ['result' => false];
            }
        }

        return $result;
    }

    /**
     * SaveQuoteItem
     *
     * @param \Magento\Quote\Model\Quote\Item $quoteItem
     * @return void
     */
    public function saveQuoteItem($quoteItem)
    {
        $this->_cartItemRepository->save($quoteItem);
    }

    /**
     * GetExceptionMessage
     *
     * @param \Exception $e
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getExceptionMessage($e)
    {
        return __('Sorry, we cannot save product in cart.');
    }

    /**
     * @return []
     */
    public function getDistanceOptions()
    {
        $getDefaultDistanceRange = $this->getDefaultDistanceRange();

        $result = [];
        if ($getDefaultDistanceRange) {
            $range = explode(',', $getDefaultDistanceRange);

            if (!empty($range)) {
                foreach ($range as $item) {
                    $result[] = ['value' => $item, 'label' => $item];
                }
            }
        }

        return $result;
    }

    /**
     * Log error
     *
     * @param string $message
     * @return bool
     */
    public function logError($message)
    {
        $this->_logger->error('COLLECT ERROR ==>> ' . $message);

        return true;
    }

    /**
     * Get stock update interface
     *
     * @return bool
     */
    public function hasStockUpdateInterface()
    {
        return
            $this->isFullVariation()
            && $this->scopeConfig->isSetFlag(
                self::XML_COLLECT_STOCK_UPDATE_INTERFACE,
                ScopeInterface::SCOPE_WEBSITE
            );
    }

    /**
     * Get stock update interface
     *
     * @return mixed|string
     */
    public function getDefaultDistanceRange()
    {
        return $this->scopeConfig->getValue(
            self::XML_DEFAULT_DISTANCE_RANGE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Get collect method title
     *
     * @return mixed|string
     */
    public function getCollectMethodTitle()
    {
        return $this->scopeConfig->getValue(
            self::XML_COLLECT_METHOD_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get collect method title
     *
     * @return mixed|string
     */
    public function getCollectMethodName()
    {
        return $this->scopeConfig->getValue(
            self::XML_COLLECT_METHOD_NAME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Whether collect is enable
     *
     * @return bool
     */
    public function isCollectEnable()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_COLLECT_METHOD_ENABLE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return bool
     */
    public function isEnableSingleStoreInCartRestriction()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_ENABLE_SINGLE_STORE_IN_CART_RESTRICTION,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Whether collect is enable on Product Display Page.
     *
     * @return bool
     */
    public function isCollectEnableOnPdp()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_COLLECT_METHOD_ENABLE_ON_PDP,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Whether collect is enable on Cart Page.
     *
     * @return bool
     */
    public function isCollectEnableOnCart()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_COLLECT_METHOD_ENABLE_ON_CART,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Whether collect is enable on checkout
     *
     * @return bool
     */
    public function isCollectEnableOnCheckout()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_COLLECT_METHOD_ENABLE_ON_CHECKOUT,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * C&C variation type
     *
     * @return string
     */
    public function getVariationType()
    {
        return $this->scopeConfig->getValue(self::XML_COLLECT_VARIATION, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * Get message if product is available in previously added item's store
     *
     * @param string $storeName
     * @return string
     */
    public function getMessageProductIsAvailable($storeName)
    {
        $message = $this->scopeConfig->getValue(self::XML_PRODUCT_AVAILABLE_MESSAGE);

        return $this->addStoreNameToMessage($storeName, $message);
    }

    /**
     * Get message if product is not available in previously added item's store
     *
     * @param string $storeName
     * @return string
     */
    public function getMessageProductIsNotAvailable($storeName)
    {
        $message = $this->scopeConfig->getValue(self::XML_PRODUCT_NOT_AVAILABLE_MESSAGE);

        return $this->addStoreNameToMessage($storeName, $message);
    }

    /**
     * Add store name to message.
     *
     * @param string $storeName
     * @param string $message
     * @return string
     */
    public function addStoreNameToMessage($storeName, $message)
    {
        return str_replace(self::STORE_NAME_SEARCH_CONDITION, $storeName, $message);
    }

    /**
     * Check if C&C variation type is full
     *
     * @return string
     */
    public function isFullVariation()
    {
        return self::VARIATION_TYPE_FULL === $this->getVariationType();
    }

    /**
     * Check if C&C variation type is single
     *
     * @return string
     */
    public function isSingleVariation()
    {
        return self::VARIATION_TYPE_SINGLE === $this->getVariationType();
    }

    /**
     * Check if C&C variation type is Single C&C for Cart
     *
     * @return string
     */
    public function isSingleCartVariation()
    {
        return self::VARIATION_TYPE_CART_SINGLE === $this->getVariationType();
    }

    /**
     * GetCollectPlaceById
     *
     * @param string $collectPlaceId
     * @param string $storageName
     * @return bool|\Digidirect\Collect\Api\Data\CollectPlaceInterface
     */
    public function getCollectPlaceById($collectPlaceId, $storageName)
    {
        return $this->_storageHandler->getCollectPlaceById($collectPlaceId, $storageName);
    }

    /**
     * Get order collect description
     *
     * @param \Magento\Sales\Model\Order $order
     * @return []
     */
    public function getOrderCollectDescription($order)
    {
        $description = [];
        $isCollect = false;

        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getCollectPlaceId()) {
                $isCollect = true;
            }
        }

        if (!$isCollect) {
            return $description;
        }

        if ($order->getShippingDescription()) {
            $description[] = $order->getShippingDescription();
        }

        $description[] = $this->getCollectMethodTitle() . ' - ' . $this->getCollectMethodName();

        return $description;
    }

    /**
     * GetIsCollectItems
     *
     * @param string $quoteId
     * @return bool
     */
    public function isCollectItems($quoteId)
    {
        return $this->checkExistingItems($quoteId, self::DELIVERY_TYPE_COLLECT);
    }

    /**
     * GetIsDeliveryItems
     *
     * @param string $quoteId
     * @return bool
     */
    public function isDeliveryItems($quoteId)
    {
        return $this->checkExistingItems($quoteId, self::DELIVERY_TYPE_DELIVER);
    }

    /**
     * Whether shipping method is collect
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    public function hasQuoteCollectShippingMethod(\Magento\Quote\Model\Quote $quote = null)
    {
        if (null === $quote && $this->_checkoutSession->hasQuote()) {
            $quote = $this->_checkoutSession->getQuote();
        }

        if ($quote && ($shippingMethod = $quote->getShippingAddress()->getShippingMethod())
            && $this->isCollectShippingMethod($shippingMethod)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Whether shipping method is collect
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function hasOrderCollectShippingMethod(\Magento\Sales\Model\Order $order = null)
    {
        if ($order && ($shippingMethod = $order->getShippingMethod())
            && $this->isCollectShippingMethod($shippingMethod)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Is string contain collect shipping method name
     *
     * @param string $shippingMethod
     * @return bool
     */
    public function isCollectShippingMethod($shippingMethod)
    {
        return \Digidirect\Collect\Model\Carrier\Collectcarrier::COLLECT_SHIPPING_METHOD == $shippingMethod;
    }

    /**
     * Is string contain collect shipping method name
     *
     * @param string $code
     * @return bool
     */
    public function isCollectCarrierCode($code)
    {
        return \Digidirect\Collect\Model\Carrier\Collectcarrier::COLLECT_CARRIER_CODE == $code;
    }

    /**
     * CheckExistingItems
     *
     * @param string $quoteId
     * @param string $type
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function checkExistingItems($quoteId, $type)
    {
        $key = $quoteId . '-' . $type;
        if (!isset($this->_existingItems[$key])) {
            $deliver = $collect = false;
            /** @var  \Magento\Quote\Model\Quote $quote */
            if (!$this->isAreaForActiveQuote()) {
                $quote = $this->_quoteRepository->get($quoteId);
            } else {
                $quote = $this->_quoteRepository->getActive($quoteId);
            }

            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                /** @var $quoteItem \Magento\Quote\Model\Quote\Item */

                if (!$quoteItem->getParentItemId()) {
                    if (!$deliver && (!$quoteItem->getCollectPlaceId() && !$quoteItem->getProduct()->isVirtual())) {
                        $deliver = true;
                    }

                    if (!$collect && $quoteItem->getCollectPlaceId()) {
                        $collect = true;
                    }

                    if ($deliver && $collect) {
                        break;
                    }
                }
            }
            $this->_existingItems[$quoteId . '-' . self::DELIVERY_TYPE_DELIVER] = $deliver;
            $this->_existingItems[$quoteId . '-' . self::DELIVERY_TYPE_COLLECT] = $collect;
        }

        $logger = \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class);
        $logger->debug('digidebug5 key = ' . $key);
        $logger->debug('digidebug6 existing items = ' . print_r($this->_existingItems, true));

        return $this->_existingItems[$key];
    }

    /**
     * @return bool
     */
    public function isAreaForActiveQuote()
    {
        if ($this->_state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            return false;
        }

        return true;
    }

    /**
     * GetPlacesUrl
     *
     * @param array $params
     * @return string
     */
    public function getPlacesUrl($params = [])
    {
        return $this->_getUrl('collectplace/place/getplaces', $params);
    }

    /**
     * Get Change Place Url
     *
     * @return string
     */
    public function getChangePlaceUrl()
    {
        return $this->_getUrl('collectplace/place/change');
    }

    /**
     * Get Deliver Instead Url
     *
     * @param bool $quoteItemId
     * @return string
     */
    public function getDeliverInsteadUrl($quoteItemId = null)
    {
        $params = ['deliver_method' => self::DELIVERY_TYPE_DELIVER];
        if ($quoteItemId) {
            $params['quote_item_id'] = $quoteItemId;
        }

        return $this->_getUrl('collectplace/place/change', $params);
    }

    /**
     * @param null|int $quoteId
     * @return bool
     */
    public function hasDeliveryItemInCart($quoteId = null)
    {
        if (!$quoteId) {
            $quoteId = $this->_checkoutSession->getQuoteId();
        }
        return $quoteId ? $this->isDeliveryItems($quoteId) : false;
    }

    /**
     * @param null|int $quoteId
     * @return bool
     */
    public function hasCollectItemInCart($quoteId = null)
    {
        if (!$quoteId) {
            $quoteId = $this->_checkoutSession->getQuoteId();
        }

        return $quoteId ? $this->isCollectItems($quoteId) : false;
    }

    /**
     * @return int
     */
    public function getPlacesOnPage()
    {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PLACES_ON_PAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param array $skus
     * @param int $qty
     * @return array
     */
    public function prepareSkuQtyArray(array $skus, $qty)
    {
        $skuQty = [];
        foreach ($skus as $sku) {
            $skuQty[$sku] = $qty;
        }

        return $skuQty;
    }

    /**
     * @param array $quoteItems
     * @return array
     */
    public function getSkuToQtyByItems($quoteItems)
    {
        $skuToQty = [];
        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                foreach ($quoteItem->getChildren() as $childItem) {
                    $sku = $childItem->getSku();
                    $result[$sku] = $sku;
                    if (!isset($skuToQty[$sku])) {
                        $skuToQty[$sku] = 0;
                    }
                    $skuToQty[$sku] += $childItem->getQty();
                }
                continue;
            }
            $sku = $quoteItem->getSku();
            $result[$sku] = $sku;
            if (!isset($skuToQty[$sku])) {
                $skuToQty[$sku] = 0;
            }
            $skuToQty[$sku] += $quoteItem->getQty();
        }

        return $skuToQty;
    }
}
