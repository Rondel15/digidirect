<?php

namespace Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\Catalog\Model;

use Digidirect\ExtendedCatalogPriceRule\Helper\Config as ConfigHelper;
use Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\CatalogRule\Observer\RulePricesStorage;
use Magento\Customer\Model\Group;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductRepositoryPlugin
 * @package Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\Catalog\Model
 */
class ProductRepositoryPlugin
{
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RulePricesStorage
     */
    protected $rulePricesStorage;

    /**
     * @var RuleFactory
     */
    protected $resourceRuleFactory;

    /**
     * @var ProductExtensionInterfaceFactory
     */
    protected $extensionFactory;

    /**
     * ProductRepositoryPlugin constructor.
     * @param ConfigHelper $configHelper
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     * @param RulePricesStorage $rulePricesStorage
     * @param RuleFactory $resourceRuleFactory
     * @param ProductExtensionInterfaceFactory $extensionFactory
     */
    public function __construct(
        ConfigHelper $configHelper,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        RulePricesStorage $rulePricesStorage,
        RuleFactory $resourceRuleFactory,
        ProductExtensionInterfaceFactory $extensionFactory
    ) {
        $this->configHelper = $configHelper;
        $this->localeDate = $localeDate;
        $this->storeManager = $storeManager;
        $this->rulePricesStorage = $rulePricesStorage;
        $this->resourceRuleFactory = $resourceRuleFactory;
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param ProductRepository $object
     * @param $result
     * @return mixed
     */
    public function afterGet(ProductRepository $object, $result)
    {
        if ($result instanceof Product && $this->configHelper->isSetDynamicPrice()) {
           $result = $this->setDynamicPrice($result);
        }

        //$result->setSku(substr($result->getSku(), 0, 50));

        return $result;
    }

    /**
     * @param ProductRepository $object
     * @param $result
     * @return mixed
     */
    public function afterGetList(ProductRepository $object, $result)
    {
        if ($result instanceof \Magento\Framework\Api\SearchResults && $this->configHelper->isSetDynamicPrice()) {
            $products = $result->getItems();
            $finalProductsArray = [];
            foreach ($products as $product) {
                $finalProductsArray[] = $this->setDynamicPrice($product);
            }
            $result->setItems($finalProductsArray);
        }

        return $result;
    }

    /**
     * @param $product
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setDynamicPrice($product)
    {
        if ($finalPrice = $this->getDynamicPrice($product)) {
            $finalPrice = (float)$this->getDynamicPrice($product);
            $extensionAttributes = $product->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setDynamicPrice($finalPrice);
            $product->setExtensionAttributes($extensionAttributes);
        }

        return $product;
    }

    /**
     * @param $product
     * @return bool|false|float|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDynamicPrice($product)
    {
        $pId = $product->getId();
        $storeId = $product->getStoreId();
        $date = $this->localeDate->scopeDate($storeId);
        $wId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $gId = Group::NOT_LOGGED_IN_ID;
        $finalPrice = false;

        $key = "{$date->format('Y-m-d H:i:s')}|{$wId}|{$gId}|{$pId}";
         if (!$this->rulePricesStorage->hasRulePrice($key)) {
            $rulePrice = $this->resourceRuleFactory->create()->getRulePrice($date, $wId, $gId, $pId);
            $this->rulePricesStorage->setRulePrice($key, $rulePrice);
        }
        if ($this->rulePricesStorage->getRulePrice($key) !== false) {
             $finalPrice = !empty($product->getData('final_price')) ?
                 min($product->getData('final_price'), $this->rulePricesStorage->getRulePrice($key)) :
                 $this->rulePricesStorage->getRulePrice($key);
        }

        return $finalPrice;
    }
}
