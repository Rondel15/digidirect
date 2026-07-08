<?php

namespace Digidirect\ShippingAvailabilityCheck\Model;

use Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class QuoteManagement
 *
 * @package Digidirect\ShippingAvailabilityCheck\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuoteManagement implements \Digidirect\ShippingAvailabilityCheck\Api\QuoteManagementInterface
{
    const CACHE_TAG = 'shipping_availability_check_quote';

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilterInterfaceFactory
     */
    protected $cartRequestInfoFilterFactory;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var Processor\Data
     */
    protected $processor;

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cacheManager;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * QuoteManagement constructor.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\DataObject\Factory $dataObjectFactory
     * @param \Magento\Checkout\Model\Cart\RequestInfoFilterInterfaceFactory $cartRequestInfoFilterFactory
     * @param \Digidirect\ShippingAvailabilityCheck\Api\QuoteRepositoryInterface $cartRepository
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $session
     * @param Processor\Data $processor
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\DataObject\Factory $dataObjectFactory,
        \Magento\Checkout\Model\Cart\RequestInfoFilterInterfaceFactory $cartRequestInfoFilterFactory,
        \Digidirect\ShippingAvailabilityCheck\Api\QuoteRepositoryInterface $cartRepository,
        \Digidirect\ShippingAvailabilityCheck\Model\QuoteFactory $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $session,
        \Digidirect\ShippingAvailabilityCheck\Model\Processor\Data $processor,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Catalog\Helper\Product $productHelper = null
    ) {
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->quoteRepository = $cartRepository;
        $this->cartRequestInfoFilterFactory = $cartRequestInfoFilterFactory;
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->session = $session;
        $this->processor = $processor;
        $this->_cacheManager = $cacheManager;
        $this->productHelper = $productHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Catalog\Helper\Product::class);
    }

    /**
     * @param ProductDataInterface $productData
     * @param string $hash
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @param int|null $customerId
     * @param null $product
     * @return Quote|null
     */
    public function createQuote(
        $productData,
        $hash,
        $address,
        $customerId = null,
        $product = null
    ) {
        $storeId = $this->storeManager->getStore()->getId();

        try {
            $quote = $this->quoteRepository->getForProduct($hash, $customerId);
        } catch (NoSuchEntityException $e) {
            $quote = null;
        }

        if (!$quote) {
            /** @var Quote $quote */
            $quote = $this->quoteFactory->create();

            if ($customerId) {
                $customer = $this->customerRepository->getById($customerId);
                $quote->setCustomer($customer)
                    ->setCustomerIsGuest(false);
            }

            $quote->setStoreId($storeId)
                ->setIsMultiShipping(false)
                ->setIsActive(false);

            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->addData($address->getData());
            $quote->setShippingAddress($shippingAddress);

            if ($productData->getProductId() || $productData->getSku()) {
                $this->addProductToQuote($quote, $productData, $storeId, $product);
            }
            $quote->setShippingAvailabilityCheckHash($hash);
            $this->quoteRepository->save($quote);
        }

        return $quote;
    }

    /**
     * @param ProductDataInterface $productData
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @param int|null $customerId
     * @return Quote|null
     */
    public function getExistedOrCreateNewQuote($productData, $address, $customerId = null)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$customerId) {
            $customerId = $this->customerSession->getCustomerId();
        }
        $product = $sku = '';

        /** @var \Magento\Catalog\Model\Product $product */
        /** @var string $sku */
        if ($productData->getProductId()) {
            $product = $this->productRepository->getById($productData->getProductId(), false, $storeId, true);
            $sku = $product->getSku();
        } elseif ($productData->getSku()) {
            //load product by sku if only quote is not exist
            $sku = $productData->getSku();
        }

        /** create unique hash to identify quote for specific product */
        $hash = $this->processor->getUniqueHash($sku, $storeId, $customerId, $productData->getParams());

        if ($quoteId = $this->_cacheManager->load($hash)) {
            try {
                $quote = $this->quoteRepository->get($quoteId);

                return $quote;
            } catch (NoSuchEntityException $e) {
                $this->_cacheManager->remove($hash);
            }
        }

        $quote = $this->createQuote($productData, $hash, $address, $customerId, $product);
        $quoteId = $quote->getId();
        $this->_cacheManager->save(
            $quoteId,
            $hash,
            [
                self::CACHE_TAG
            ]
        );

        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param ProductDataInterface $productData
     * @param int $storeId
     * @param null|\Magento\Catalog\Model\Product $product
     * @throws LocalizedException
     * @return void
     */
    public function addProductToQuote(
        \Magento\Quote\Api\Data\CartInterface $quote,
        $productData,
        $storeId,
        $product = null
    ) {
        $params = $this->getProductParams($productData->getParams());
        $qty = ($params->getQty()) ?? 1;
        $params->setQty($qty);

        /** @var \Magento\Catalog\Model\Product $product */
        if (!$product) {
            if ($productData->getProductId()) {
                $product = $this->productRepository->getById($productData->getProductId(), false, $storeId, true);
            } elseif ($productData->getSku()) {
                $product = $this->productRepository->get($productData->getSku(), false, $storeId, true);
            }
        }

        $skipSalableCheck = $this->productHelper->getSkipSaleableCheck();
        $this->productHelper->setSkipSaleableCheck(true);
        $quote->setIsSuperMode(true);
        try {
            $result = $quote->addProduct($product, $params);
        } catch (\Throwable $e) {
            $result = $e->getMessage();
        } finally {
            $this->productHelper->setSkipSaleableCheck($skipSalableCheck);
        }
        /**
         * String we can get if prepare process has error
         */
        if (is_string($result)) {
            throw new \Magento\Framework\Exception\LocalizedException(__($result));
        }
    }

    /**
     * @param array $params
     * @return \Magento\Framework\DataObject
     * @throws LocalizedException
     */
    protected function getProductParams($params)
    {
        if (is_numeric($params)) {
            $params = new \Magento\Framework\DataObject(['qty' => $params]);
        } elseif (is_array($params)) {
            $params = new \Magento\Framework\DataObject($params);
        } elseif (!$params instanceof \Magento\Framework\DataObject) {
            $params = new \Magento\Framework\DataObject();
        }

        /** @var \Magento\Checkout\Model\Cart\RequestInfoFilterComposite $cartRequestInfoFilter */
        $cartRequestInfoFilter = $this->cartRequestInfoFilterFactory->create();
        $cartRequestInfoFilter->filter($params);

        return $params;
    }
}
