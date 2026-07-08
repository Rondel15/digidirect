<?php
namespace Digidirect\RelatedProduct\Block\ProductList;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Related extends \Magento\Catalog\Block\Product\ProductList\Related
{
    const ALL_CATEGORY_ID = '0';

    const DEFAULT_ALL_CATEGORY_LIMIT = 5;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @var array
     */
    protected $_categories = [];

    /**
     * @var array
     */
    protected $_categoriesPositions = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    protected $_categoriesItems;

    /**
     * @var array
     */
    protected $_productAttributes = ['required_options'];

    /**
     * @var \Digidirect\RelatedProduct\Model\ResourceModel\RelatedProduct\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Digidirect\RelatedProduct\Helper\Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_allCategoryProducts;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart
     * @param Visibility $catalogProductVisibility
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Digidirect\RelatedProduct\Model\ResourceModel\RelatedProduct\CollectionFactory $productCollectionFactory
     * @param \Digidirect\RelatedProduct\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\ResourceModel\Cart $checkoutCart,
        Visibility $catalogProductVisibility,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Digidirect\RelatedProduct\Model\ResourceModel\RelatedProduct\CollectionFactory $productCollectionFactory,
        \Digidirect\RelatedProduct\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $checkoutCart,
            $catalogProductVisibility,
            $checkoutSession,
            $moduleManager,
            $data
        );

        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_helper = $helper;
        if ($this->hasData('product_attributes')) {
            $this->_productAttributes = $this->getData('product_attributes');
        }
        $this->_productAttributes = array_merge($this->_productAttributes, $this->_helper->getShowingAttributes());
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->_coreRegistry->registry('product');
        if ($product) {
            //Compatibility with the Target Rule extension of Commerce Edition:
            $relatedIds = [];
            if ($this->moduleManager->isEnabled('Magento_TargetRule')) {
                // @codeCoverageIgnoreStart
                /**
                 * ObjectManager is used for compatibility with Community Edition
                 * @var \Magento\TargetRule\Block\Catalog\Product\ProductList\Related $targetRule
                 */
                $targetRule = ObjectManager::getInstance()->get(
                    'Magento\TargetRule\Block\Catalog\Product\ProductList\Related'
                );
                $relatedIds = array_keys($targetRule->getItemCollection());
                // @codeCoverageIgnoreEnd
            }

            $this->_itemCollection = $this->getRelatedProductCollection($product, $relatedIds)
                ->addAttributeToSelect($this->_productAttributes)
                ->setPositionOrder()
                ->addStoreFilter();

            if ($this->moduleManager->isEnabled('Magento_Checkout')) {
                $this->_addProductAttributesAndPrices($this->_itemCollection);
            }

            $aVisibility = $this->_helper->getVisibilityProductsTypes();
            if (!empty($aVisibility)) {
                $this->_itemCollection->addAttributeToFilter(
                    'visibility',
                    ['in' => $aVisibility]
                );
            }
            $this->_itemCollection->load();

            foreach ($this->_itemCollection as $product) {
                $this->_categories = array_merge($this->_categories, $product->getCategoryIds());
                $product->setDoNotUseCategoryId(true);
            }

            $this->_categories = array_unique($this->_categories);
            $this->getCategoriesAssignedRelatedProducts(true, null, $this->_itemCollection);
            $this->_categoriesPositions = $this->_itemCollection->getCategoryPositions($this->_categories);
        }
        return $this;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $relatedIds
     * @return \Digidirect\RelatedProduct\Model\ResourceModel\RelatedProduct\Collection
     */
    public function getRelatedProductCollection(\Magento\Catalog\Model\Product $product, array $relatedIds = [])
    {
        $linkModel = $product->getLinkInstance()->useRelatedLinks();
        $collection = $this->_productCollectionFactory->create();
        $collection->setIsStrongMode()->setProduct($product);

        if (!empty($relatedIds)) {
            $collection->addProductsFilter($relatedIds);
        } else {
            $collection->setLinkModel($linkModel);
        }

        return $collection;
    }

    /**
     * Get category list of products
     *
     * @param bool $isActive
     * @param bool|string $sortBy
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $products
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoriesAssignedRelatedProducts(
        $isActive = true,
        $sortBy = null,
        ProductCollection $products = null
    ) {
        if (!$this->_categoriesItems) {
            $productCategoriesCollection = $this->_categoryCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $this->_categories])
                ->addAttributeToSelect(['entity_id', 'name']);

            $this->addCategoryAttribute($productCategoriesCollection);

            if ($isActive) {
                $productCategoriesCollection->addIsActiveFilter();
            }
            if ($sortBy !== null) {
                $productCategoriesCollection->addOrderField($sortBy);
            }

            if (null !== $products && $this->_helper->displayOneCategory()) {
                $this->_categories = [];
                foreach ($products as $product) {
                    $categoriesByLevel = [];
                    foreach ($productCategoriesCollection as $category) {
                        if (in_array($category->getId(), $product->getCategoryIds())) {
                            $categoriesByLevel[(int)$category->getLevel()][] = $category;
                        }
                    }

                    if (!empty($categoriesByLevel)) {
                        krsort($categoriesByLevel);
                        foreach (reset($categoriesByLevel) as $deepestCategory) {
                            if (!in_array($deepestCategory->getId(), $this->_categories)) {
                                $this->_categories[] = $deepestCategory->getId();
                                $this->_categoriesItems[] = $deepestCategory;
                            }
                        }
                    }
                }
            } else {
                $this->_categories = $productCategoriesCollection->getAllIds();
                $this->_categoriesItems = $productCategoriesCollection;
            }
        }
        return $this->_categoriesItems;
    }

    /**
     * Retrieve featured product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $featuredImage
     * @param string $imageType
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getFeatureImageHtml($product, $featuredImage, $imageType, $attributes = [])
    {
        $imageBlock = $this->getImage($product, $imageType, $attributes);

        return $imageBlock->setImageUrl($featuredImage->getData($imageType))->toHtml();
    }

    /**
     * @return array
     */
    public function getCategoryAttributes()
    {
        return $this->_helper->getShowingCategoryAttributes();
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    protected function addCategoryAttribute (AbstractCollection $collection)
    {
        $showingAttributes = $this->_helper->getShowingCategoryAttributes();
        if (!empty($showingAttributes)) {
            $collection->addAttributeToSelect($showingAttributes);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAllCategoryProducts()
    {
        if (null === $this->_allCategoryProducts) {
            $this->_allCategoryProducts = [];
            $allCategoryLimit = (int)($this->getAllCategoryLimit() ?: static::DEFAULT_ALL_CATEGORY_LIMIT);
            $allCategoryCounter = 0;

            for ($i = 0; $i < $allCategoryLimit; $i++) {
                foreach ($this->getCategoriesAssignedRelatedProducts() as $category) {
                    if ($allCategoryLimit === $allCategoryCounter) {
                        break(2);
                    }

                    $items = $this->_categoriesPositions[$category->getId()] ?? [];
                    foreach ($items as $item => $position) {
                        if (!in_array($item, $this->_allCategoryProducts)) {
                            $this->_allCategoryProducts[] = $item;
                            $allCategoryCounter++;
                            break;
                        }
                    }
                }
            }
        }

        return $this->_allCategoryProducts;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isProductInAllCategory(Product $product)
    {
        return ($this->getDisplayAllCategory() && in_array($product->getId(), $this->getAllCategoryProducts()));
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getVisibleProductCategories(Product $product)
    {
        $productCategoryIds = [];
        foreach ($this->getCategoriesAssignedRelatedProducts() as $category) {
            if (!empty($this->_categoriesPositions[$category->getId()])
                && array_key_exists($product->getId(), $this->_categoriesPositions[$category->getId()])
            ) {
                $productCategoryIds[] = $category->getId();
            }
        }

        if ($this->isProductInAllCategory($product)) {
            $productCategoryIds[] = static::ALL_CATEGORY_ID;
        }
        
        foreach ($productCategoryIds as $index => &$value) {
            $array[$index] = "cat-" . $value;
        }
        
        $finalCats = implode (" ", $array);

        return $finalCats;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getProductPositionsInCategories(Product $product)
    {
        $positions = [];
        $productCategoryIds = $this->getVisibleProductCategories($product);
        foreach ($productCategoryIds as $sortOrder => $productCategoryId) {
            if (static::ALL_CATEGORY_ID == $productCategoryId) {
                $positions[] = (int)array_search($product->getId(), $this->_allCategoryProducts);
            } else {
                $positions[] = $this->_categoriesPositions[$productCategoryId][$product->getId()] ?? 0;
            }
        }

        return $positions;
    }
}
