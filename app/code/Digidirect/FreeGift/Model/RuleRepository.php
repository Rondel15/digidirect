<?php

namespace Digidirect\FreeGift\Model;

use Digidirect\FreeGift\Api\RuleRepositoryInterface;
use Digidirect\FreeGift\Api\Data\RuleInterface;
use Digidirect\FreeGift\Model\ResourceModel\Rule as ResourceRule;
use Digidirect\FreeGift\Model\ResourceModel\Rule\CollectionFactory as ResourceRuleCollectionFactory;
use Digidirect\FreeGift\Model\ResourceModel\Rule\Collection as FreeGiftCollection;
use Digidirect\FreeGift\Model\Rule\Validator;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Config as ProductConfig;
use Magento\CatalogInventory\Helper\Stock as StockFilter;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class RuleRepository
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleRepository implements RuleRepositoryInterface
{
    const ADD_ENABLE_ON_PDP_FILTER = 1;

    const REMOVE_ENABLE_ON_PDP_FILTER = 0;

    /**
     * @var ResourceRule
     */
    protected $_resource;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var ResourceRuleCollectionFactory
     */
    protected $_resourceRuleCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * @var StockFilter
     */
    protected $_stockFilter;

    /**
     * @var Validator
     */
    protected $_ruleValidator;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var []
     */
    protected $validRules = [];

    /**
     * RuleRepository constructor.
     *
     * @param ResourceRule $resource
     * @param RuleFactory $ruleFactory
     * @param ResourceRuleCollectionFactory $resourceRuleCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductConfig $catalogConfig
     * @param StockFilter $stockFilter
     * @param Validator $ruleValidator
     * @param StoreManagerInterface $storeManager
     * @param CustomerSession $customerSession
     */
    public function __construct(
        ResourceRule $resource,
        RuleFactory $ruleFactory,
        ResourceRuleCollectionFactory $resourceRuleCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductConfig $catalogConfig,
        StockFilter $stockFilter,
        Validator $ruleValidator,
        StoreManagerInterface $storeManager,
        CustomerSession $customerSession
    ) {
        $this->_resource = $resource;
        $this->_ruleFactory = $ruleFactory;
        $this->_resourceRuleCollectionFactory = $resourceRuleCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_stockFilter = $stockFilter;
        $this->_ruleValidator = $ruleValidator;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
    }

    /**
     * Save Rule data
     *
     * @param RuleInterface $rule
     * @return RuleInterface
     * @throws CouldNotSaveException
     */
    public function save(RuleInterface $rule)
    {
        try {
            $this->_resource->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('We can\'t save the FreeGift Rule.'), $exception);
        }
        return $rule;
    }

    /**
     * Load Rule data by given Identity
     *
     * @param int $ruleId
     * @return RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($ruleId)
    {
        $rule = $this->_ruleFactory->create();
        $this->_resource->load($rule, $ruleId);
        if (!$rule->getId()) {
            throw new NoSuchEntityException(__('FreeGift Rule with id "%1" does not exist.', $ruleId));
        }
        return $rule;
    }

    /**
     * Load Rule data by given Identity
     *
     * @param \Magento\SalesRule\Model\Rule $salesRule
     * @return RuleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadBySalesrule(\Magento\SalesRule\Model\Rule $salesRule)
    {
        $rule = $this->_ruleFactory->create();
        $this->_resource->load($rule, $salesRule->getId(), RuleInterface::FIELD_SALESRULE_ID);
        return $rule;
    }

    /**
     * Delete Rule
     *
     * @param RuleInterface $rule
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RuleInterface $rule)
    {
        try {
            $this->_resource->delete($rule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('We can\'t delete the FreeGift Rule.'), $exception);
        }
        return true;
    }

    /**
     * Delete Rule by given Identity
     *
     * @param int $ruleId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($ruleId)
    {
        return $this->delete($this->getById($ruleId));
    }

    /**
     * Load Promo Items by a product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getPromoItemsByProduct(\Magento\Catalog\Model\Product $product)
    {
        $promoItems = [];

        $validRules = $this->getAppliedFreeGiftRulesByProduct($product);
        if (!empty($validRules)) {
            foreach ($validRules as $salesRule) {
                $promoItems = array_merge($promoItems, $salesRule->getSkuArray());
            }
        }

        $promoItems = array_unique($promoItems);
        if (!empty($promoItems)) {
            $productCollection = $this->_productCollectionFactory->create();
            $this->_stockFilter->addInStockFilterToCollection($productCollection);
            $productCollection
                ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addAttributeToSelect(ProductInterface::VISIBILITY)
                ->addAttributeToFilter(ProductInterface::SKU, ['in' => $promoItems])
                ->addAttributeToFilter(ProductInterface::STATUS, ['eq' => Status::STATUS_ENABLED])
                ->addAttributeToFilter('entity_id', ['neq' => $product->getId()])
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addUrlRewrite();

            return $productCollection;
        }
        return [];
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @return \Digidirect\FreeGift\Model\Rule[]|mixed|null
     */
    public function getAppliedFreeGiftRulesByProduct(\Magento\Catalog\Model\Product $product = null)
    {
        if (!$product) {
            return $this->validRules;
        }

        $validRulesForProduct = $this->validRules[$product->getId()] ?? null;
        if ($validRulesForProduct) {
            return $validRulesForProduct;
        }

        $collection = $this->getRulesCollection();

        $this->validRules[$product->getId()] = $this->getValidRules($collection, $product);
        return $this->validRules[$product->getId()];
    }

    /**
     * @return \Magento\Store\Api\Data\WebsiteInterface
     */
    protected function getWebsite()
    {
        return $this->_storeManager->getWebsite();
    }

    /**
     * @param bool $addEnableOnPdpFilter
     * @param bool $addShowDescOnPdp
     * @return FreeGiftCollection
     */
    protected function getRulesCollection($addEnableOnPdpFilter = true, $addShowDescOnPdp = false)
    {
        $collection = $this->_resourceRuleCollectionFactory->create()
            ->setValidationFilter($this->getWebsite()->getId(), $this->_customerSession->getCustomerGroupId())
            ->addFieldToFilter('simple_action', 'freegift_items')
            ->joinFreeGift($addEnableOnPdpFilter, $addShowDescOnPdp);

        return $collection;
    }

    /**
     * @param FreeGiftCollection $collection
     * @param \Magento\Catalog\Model\Product|null $product
     * @return []
     */
    protected function getValidRules(FreeGiftCollection $collection, \Magento\Catalog\Model\Product $product = null)
    {
        if (null === $product) {
            return [];
        }
        $website = $this->_storeManager->getWebsite();
        $validRules[$product->getId()] = [];
        foreach ($collection as $salesRule) {
            if ($this->_ruleValidator->validateProduct($product, $salesRule, $website->getId())) {
                /** @var \Digidirect\FreeGift\Model\Rule $salesRule */
                $validRules[$product->getId()][] = $salesRule;
            }
        }

        return $validRules[$product->getId()];
    }

    /**
     * @param \Magento\Catalog\Model\Product|null $product
     * @return array
     */
    public function getRulesDescriptionsByProduct(\Magento\Catalog\Model\Product $product = null)
    {
        $descriptions = [];
        if (!$product) {
            return $descriptions;
        }

        $validRules = $this->getValidRules(
            $this->getRulesCollection(false, true),
            $product
        );
        foreach ($validRules as $validRule) {
            /** @var \Digidirect\FreeGift\Model\Rule $validRule */
            if ($validRule->getShowDescOnPdp()) {
                $descriptions[] = [
                    'label' => $validRule->getDescriptionLabel(),
                    'description' => $validRule->getDescription(),
                ];
            }
        }
        return $descriptions;
    }
}
