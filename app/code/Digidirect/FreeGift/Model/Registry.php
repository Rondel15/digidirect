<?php

namespace Digidirect\FreeGift\Model;

use Magento\Catalog\Model\Product\Type as ProductType;

class Registry extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var bool
     */
    protected $_hasItems = false;

    /**
     * @var bool
     */
    protected $_locked = false;

    /**
     * @var array
     */
    protected $_isHandled = [];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_giftItem;

    /**
     * @var \Digidirect\FreeGift\Helper\Messages
     */
    protected $_giftMessagesHelper;

    /**
     * @var \Digidirect\FreeGift\Helper\Config
     */
    protected $_configHelper;

    /**
     * @var array
     */
    protected $_autoAddProductTypes = [
        ProductType::TYPE_SIMPLE,
        ProductType::TYPE_VIRTUAL,
        'giftcard',
    ];

    /**
     * Registry constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Checkout\Model\Session $resourceSession
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Digidirect\FreeGift\Model\Cart\Item $giftItem
     * @param \Digidirect\FreeGift\Helper\Messages $giftMessagesHelper
     * @param \Digidirect\FreeGift\Helper\Config $configHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Digidirect\FreeGift\Model\Cart\Item $giftItem,
        \Digidirect\FreeGift\Helper\Messages $giftMessagesHelper,
        \Digidirect\FreeGift\Helper\Config $configHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_checkoutSession = $resourceSession;
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_giftItem = $giftItem;
        $this->_giftMessagesHelper = $giftMessagesHelper;
        $this->_configHelper = $configHelper;
    }

    /**
     * @param int $ruleId
     * @return bool
     */
    public function getApplyAttempt($ruleId)
    {
        if (isset($this->_isHandled[$ruleId])) {
            return false;
        }

        $this->_isHandled[$ruleId] = true;
        return true;
    }

    /**
     * @param array $skuArray
     * @param int $qty
     * @param int $ruleId
     * @param bool $isTypeOneOf
     * @param bool $isHiddenForCustomer
     * @return bool
     */
    public function addFreeGiftItem(array $skuArray, $qty, $ruleId, $isTypeOneOf = false, $isHiddenForCustomer = false)
    {
        if (!$skuArray || $this->_locked) {
            return false;
        }

        if (!$this->_hasItems) {
            $this->reset();
        }

        $this->_hasItems = true;
        $items = $this->getFreeGiftItems();

        if ($isTypeOneOf && $isHiddenForCustomer) {
            $randomSku = $skuArray[array_rand($skuArray)];
            $skuArray = [$randomSku];
        }

        if (!$isTypeOneOf || $isHiddenForCustomer) {
            $addAutomatically = $this->_configHelper->isAddProductsAutomatically();
            foreach ($skuArray as $sku) {
                $autoAdd = false;
                if ($addAutomatically || $isHiddenForCustomer) {
                    try {
                        $product = $this->_productRepository->get($sku);
                    } catch (\Throwable $exception) {
                        $product = null;
                    }
                    if (!$product || !$product->getId() || !is_array($product->getWebsiteIds())) {
                        continue;
                    }

                    $product->setData('_key_for_check_product_availability_to_auto_add', true);
                    $currentWebsiteId = $this->_storeManager->getWebsite()->getId();
                    if (!in_array($currentWebsiteId, $product->getWebsiteIds())) {
                        continue;
                    }

                    if (!$product->isInStock() || !$product->isSalable()) {
                        if (!$isHiddenForCustomer) {
                            $this->_giftMessagesHelper->addAvailabilityError($product);
                        }
                    } else {
                        if (in_array($product->getTypeId(), $this->_autoAddProductTypes)
                            && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                        ) {
                            $autoAdd = true;
                        }
                    }
                    if ($isHiddenForCustomer && !$autoAdd) {
                        continue;
                    }
                }
                if (isset($items[$sku])) {
                    $items[$sku]['qty'] += $qty;
                } else {
                    $items[$sku] = [
                        'sku' => $sku,
                        'qty' => $qty,
                        'auto_add' => $autoAdd,
                        'rule_id' => $ruleId,
                        'is_hidden_for_customer' => $isHiddenForCustomer,
                    ];
                }
            }
        } else {
            $items['_groups'][$ruleId] = [
                'sku' => $skuArray,
                'qty' => $qty,
            ];
        }

        $this->_checkoutSession->setFreegiftItems($items);
        return true;
    }

    /**
     * @return array
     */
    public function getFreeGiftItems()
    {
        $items = $this->_checkoutSession->getFreegiftItems();
        return $items ? $items : ['_groups' => []];
    }

    /**
     *
     */
    public function reset()
    {
        if ($this->_hasItems) {
            $this->_locked = true;
            return;
        }
        $this->_checkoutSession->setFreegiftItems(['_groups' => []]);
    }

    /**
     * @return array
     */
    public function getLimits()
    {
        $quote = $this->_checkoutSession->getQuote();
        $allowed = $this->getFreeGiftItems();
        foreach ($quote->getAllItems() as $item) {
            $sku = $item->getProduct()->getData('sku');
            if ($this->_giftItem->isFreeGiftItem($item)) {
                $ruleId = $this->_giftItem->getRuleId($item);
                if (isset($allowed['_groups'][$ruleId])) {
                    if ($item->getParentItem()) {
                        continue;
                    }

                    $allowed['_groups'][$ruleId]['qty'] -= $item->getQty();
                    if ($allowed['_groups'][$ruleId]['qty'] <= 0) {
                        unset($allowed['_groups'][$ruleId]);
                    }
                } else {
                    if (isset($allowed[$sku])) {
                        $allowed[$sku]['qty'] -= $item->getQty();
                        if ($allowed[$sku]['qty'] <= 0 || !empty($allowed[$sku]['is_hidden_for_customer'])) {
                            unset($allowed[$sku]);
                        }
                    }
                }
            }
        }

        return $allowed;
    }

    /**
     * @param string $sku
     * @return void
     */
    public function deleteProduct($sku)
    {
        $deletedItems = $this->_checkoutSession->getFreegiftDeletedItems();
        if (!$deletedItems) {
            $deletedItems = [];
        }
        $deletedItems[$sku] = true;
        $this->_checkoutSession->setFreegiftDeletedItems($deletedItems);
    }

    /**
     * @param string $sku
     * @return void
     */
    public function restore($sku)
    {
        $deletedItems = $this->_checkoutSession->getFreegiftDeletedItems();
        if (!$deletedItems || !isset($deletedItems[$sku])) {
            return;
        }
        unset($deletedItems[$sku]);
        $this->_checkoutSession->setFreegiftDeletedItems($deletedItems);
    }

    /**
     * @return array
     */
    public function getDeletedItems()
    {
        $deletedItems = $this->_checkoutSession->getFreegiftDeletedItems();
        if (!$deletedItems) {
            $deletedItems = [];
        }
        return $deletedItems;
    }
}
