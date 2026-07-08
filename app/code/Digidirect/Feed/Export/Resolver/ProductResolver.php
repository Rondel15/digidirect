<?php

namespace Digidirect\Feed\Export\Resolver;

use Digidirect\Feed\Export\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Tax\Model\Calculation as TaxCalculation;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Digidirect\Feed\Model\ResourceModel\Dynamic\Category\CollectionFactory as CategoryMappingCollectionFactory;
use Digidirect\Feed\Model\ResourceModel\Dynamic\Attribute\CollectionFactory as DynamicAttributeCollectionFactory;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\Data\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductResolver extends AbstractResolver
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var ProductRelation
     */
    protected $productRelation;

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var AttributeSetCollectionFactory
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var ProductAttributeCollectionFactory
     */
    protected $productAttributeCollectionFactory;

    /**
     * @var CategoryMappingCollectionFactory
     */
    protected $categoryMappingCollectionFactory;

    /**
     * @var DynamicAttributeCollectionFactory
     */
    protected $dynamicAttributeCollectionFactory;

    /**
     * Cache of loaded products
     *
     * @var array
     */
    protected static $products = [];

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute[]
     */
    protected static $attributes;

    /**
     * @var array
     */
    protected static $attributeSets;

    /**
     * @var \Digidirect\Feed\Model\Dynamic\Category[]
     */
    protected static $categoryMappings;

    /**
     * @var \Digidirect\Feed\Model\Dynamic\Attribute[]
     */
    protected static $dynamicAttributes;

    /**
     * @var string
     */
    protected static $productImageBaseUrl;

    /**
     * @var int
     */
    protected static $rootCategoryId;

    /**
     * @var bool
     */
    protected static $isInitialized;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * ProductResolver constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param PoolFactory $poolFactory
     * @param ProductFactory $productFactory
     * @param StockRegistryInterface $stockRegistry
     * @param ProductRelation $productRelation
     * @param TaxCalculation $taxCalculation
     * @param AttributeSetCollectionFactory $attributeSetCollectionFactory
     * @param ProductAttributeCollectionFactory $productAttributeCollectionFactory
     * @param CategoryMappingCollectionFactory $categoryMappingCollectionFactory
     * @param DynamicAttributeCollectionFactory $dynamicAttributeCollectionFactory
     * @param ImageHelper $imageHelper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        PoolFactory $poolFactory,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
        ProductRelation $productRelation,
        TaxCalculation $taxCalculation,
        AttributeSetCollectionFactory $attributeSetCollectionFactory,
        ProductAttributeCollectionFactory $productAttributeCollectionFactory,
        CategoryMappingCollectionFactory $categoryMappingCollectionFactory,
        DynamicAttributeCollectionFactory $dynamicAttributeCollectionFactory,
        ImageHelper $imageHelper = null
    ) {
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->productRelation = $productRelation;
        $this->taxCalculation = $taxCalculation;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
        $this->categoryMappingCollectionFactory = $categoryMappingCollectionFactory;
        $this->dynamicAttributeCollectionFactory = $dynamicAttributeCollectionFactory;
        $this->imageHelper = $imageHelper ?: ObjectManager::getInstance()->get(ImageHelper::class);

        parent::__construct($context, $storeManager, $filesystem, $poolFactory);

        if (!self::$isInitialized) {
            $this->_init();
            self::$isInitialized = true;
        }
    }

    /**
     * Load all and prepare required data.
     *
     * @return $this
     */
    protected function _init()
    {
        $emptyProduct = $this->productFactory->create();
        $entityTypeId = $emptyProduct->getResource()->getEntityType()->getEntityTypeId();
        $collection = $this->attributeSetCollectionFactory->create();
        $collection->addFieldToFilter('entity_type_id', $entityTypeId);
        $collection->load();
        $items = [];
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $item */
        foreach ($collection as $item) {
            $items[$item->getId()] = $item->getAttributeSetName();
        }
        self::$attributeSets = $items;

        $collection = $this->productAttributeCollectionFactory->create();
        $collection->load();
        $items = [];
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $item */
        foreach ($collection as $item) {
            $items[$item->getAttributeCode()] = $item;
        }
        self::$attributes = $items;

        $collection = $this->categoryMappingCollectionFactory->create();
        $collection->load();
        $items = [];
        /** @var \Digidirect\Feed\Model\Dynamic\Category $item */
        foreach ($collection as $item) {
            $items[$item->getCode()] = $item;
        }
        self::$categoryMappings = $items;

        $collection = $this->dynamicAttributeCollectionFactory->create();
        $collection->load();
        $items = [];
        /** @var \Digidirect\Feed\Model\Dynamic\Attribute $item */
        foreach ($collection as $item) {
            $items[$item->getCode()] = $item;
        }
        self::$dynamicAttributes = $items;

        //All this magic for return image url without CDN
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, false)
            . $this->filesystem->getUri(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        self::$productImageBaseUrl = $baseUrl . '/' . $emptyProduct->getMediaConfig()->getBaseMediaUrlAddition() . '/';

        self::$rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getAttributes()
    {
        $result = [
            'entity_id' => 'Product Id',
            'is_in_stock' => 'Is In Stock',
            'qty' => 'Qty',
            'image' => 'Image',
            'url' => 'Product Url',
            'category' => 'Category Name',
            'category.entity_id' => 'Category Id',
            'category.path' => 'Category Path (Category > Sub Category)',
            'gallery[0]' => 'Image 2',
            'gallery[1]' => 'Image 3',
            'gallery[2]' => 'Image 4',
            'gallery[3]' => 'Image 5',
            'attribute_set' => 'Attribute Set',
            'type_id' => 'Product Type',
            'price' => 'Price',
            'regular_price' => 'Regular Price',
            'special_price' => 'Special Price',
            'final_price' => 'Final Price',
            'tax_rate' => 'Tax Rate',
        ];

        foreach (self::$attributes as $code => $attribute) {
            if (isset($result[$code])) {
                continue;
            }
            if ($storeLabel = $attribute->getStoreLabel()) {
                $result[$code] = $storeLabel . ' [' . $code . ']';
            }
        }

        foreach (self::$categoryMappings as $mapping) {
            $label = $mapping->getName();
            $result['mapping:' . $mapping->getCode()] = __('Category Mapping') . ': ' . $label;
        }

        foreach (self::$dynamicAttributes as $attribute) {
            $label = $attribute->getName();
            $result['dynamic:' . $attribute->getCode()] = __('Dynamic Attribute') . ': ' . $label;
        }

        return $result;
    }

    /**
     * Return full url for product
     *
     * @param Product $product
     *
     * @return string
     */
    public function getUrl($product)
    {
        $url = $product->getProductUrl();

        $getParams = [];

        $feed = $this->getFeed();

        if ($feed && $feed->getReportEnabled()) {
            $getParams['ff'] = $feed->getId();
            $getParams['fp'] = $product->getId();
        }

        $utmMap = [
            'utm_source' => 'ga_source',
            'utm_medium' => 'ga_medium',
            'utm_campaign' => 'ga_name',
            'utm_term' => 'ga_term',
            'utm_content' => 'ga_content',
        ];

        foreach ($utmMap as $key => $value) {
            if ($feed && $feed->getData($value)) {
                $getParams[$key] = $this->getFeed()->getData($value);
            }
        }

        if ($getParams) {
            $url .= strpos($url, '?') !== false ? '&' : '?';
            $url .= http_build_query($getParams);
        }

        return $url;
    }

    /**
     * Return full url to image
     *
     * @param Product $product
     * @return string
     */
    public function getImage($product)
    {
        if ($product->getImage()) {
            return $this->getImageUrl($product->getImage(), $product);
        }

        return '';
    }

    /**
     * Return full url to thumbnail
     *
     * @param Product $product
     * @return string
     */
    public function getThumbnail($product)
    {
        if ($product->getThumbnail()) {
            return $this->getImageUrl($product->getThumbnail(), $product, 'product_thumbnail_image');
        }

        return '';
    }

    /**
     * Return full url to small image
     *
     * @param Product $product
     * @return string
     */
    public function getSmallImage($product)
    {
        if ($product->getSmallImage()) {
            return $this->getImageUrl($product->getSmallImage(), $product, 'product_small_image');
        }

        return '';
    }

    /**
     * Return list of gallery images
     *
     * @param Product $product
     * @return array
     */
    public function getGallery($product)
    {
        $gallery = [];

        $galleryImages = $product->getMediaGalleryImages();

        /** @var \Magento\Framework\DataObject $galleryImage */
        if (is_array($galleryImages) || $galleryImages instanceof \Traversable) {
            foreach ($galleryImages as $galleryImage) {
                $gallery[] = $this->getImageUrl($galleryImage->getData('file'), $product);
            }
        }

        return $gallery;
    }

    /**
     * Price
     *
     * @param Product $product
     * @return float
     */
    public function getPrice($product)
    {
        return $product->getPrice();
    }

    /**
     * Final Price
     *
     * @param Product $product
     * @return float
     */
    public function getRegularPrice($product)
    {
        return $product->getPriceInfo()->getPrice('regular_price')->getValue();
    }

    /**
     * Final Price
     *
     * @param Product $product
     * @return float
     */
    public function getSpecialPrice($product)
    {
        return $product->getPriceInfo()->getPrice('special_price')->getValue();
    }

    /**
     * Final Price
     *
     * @param Product $product
     * @return float
     */
    public function getFinalPrice($product)
    {
        return $product->getPriceInfo()->getPrice('final_price')->getValue();
    }

    /**
     * Tax Rate
     *
     * @param Product $product
     * @return float
     */
    public function getTaxRate($product)
    {
        $request = $this->taxCalculation->getRateRequest(null, null, null, $this->getFeed()->getStoreId());
        $request->setData('product_class_id', $product->getTaxClassId());

        return $this->taxCalculation->getRate($request);
    }

    /**
     * Return product QTY
     *
     * @param Product $product
     * @return int
     */
    public function getQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());

        return $stockItem->getQty();
    }

    /**
     * Return product stock status
     *
     * @param Product $product
     * @return int
     */
    public function getIsInStock($product)
    {
        return $this->getQty($product) ? true : false;
    }

    /**
     * Attribute set name
     *
     * @param Product $product
     * @return string
     */
    public function getAttributeSet($product)
    {
        $attrSetId = $product->getAttributeSetId();
        return isset(self::$attributeSets[$attrSetId]) ? self::$attributeSets[$attrSetId] : false;
    }

    /**
     * Parent product model or current product
     *
     * @param Product $product
     * @return Product
     */
    public function getParent($product)
    {
        $connection = $this->productRelation->getConnection();
        $select = $connection->select();
        $select->from(['main_table' => $this->productRelation->getMainTable()], [])
            ->join(
                ['e' => $connection->getTableName('catalog_product_entity')],
                'e.row_id = main_table.parent_id',
                ['e.entity_id']
            )
            ->where('child_id = ?', $product->getId())
            ->limit(1);
        $parentId = $connection->fetchOne($select);

        if ($parentId) {
            $parent = $this->productFactory->create()->setId($parentId);
            return $this->getProduct($parent);
        }

        return $product;
    }

    /**
     * For simple products
     *
     * @param Product $product
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAssociatedProducts($product)
    {
        return [];
    }

    /**
     * Related products
     *
     * @param Product $product
     * @return array
     */
    public function getRelatedProducts($product)
    {
        return $product->getRelatedProducts();
    }

    /**
     * CrossSell products
     *
     * @param Product $product
     * @return array
     */
    public function getCrossSellProducts($product)
    {
        return $product->getCrossSellProducts();
    }

    /**
     * UpSell products
     *
     * @param Product $product
     * @return array
     */
    public function getUpSellProducts($product)
    {
        return $product->getUpSellProducts();
    }

    /**
     * @param array|Product|string $value
     * @param string $key
     * @return string
     */
    public function toString($value, $key = null)
    {
        if (!$key && $value instanceof Product) {
            return $value->getName();
        }

        return parent::toString($value, $key);
    }

    /**
     * Mapping model
     *
     * @param Product $product
     * @param [] $args
     * @return string
     */
    public function getMapping($product, $args)
    {
        /**
         * @var \Digidirect\Feed\Model\Dynamic\Category $mapping
         */
        $code = $args[0];
        if (!isset(self::$categoryMappings[$code])) {
            return false;
        }

        $mapping = self::$categoryMappings[$code];

        $categoryIds = array_diff($product->getCategoryIds(), [self::$rootCategoryId]);

        return $categoryIds ? $mapping->getMappingValue($categoryIds) : $mapping->getMappingValue(0);
    }

    /**
     * Collection of mappings
     *
     * @param Product $product
     * @param [] $args
     * @return string
     */
    public function getMappings($product, $args)
    {
        /**
         * @var \Digidirect\Feed\Model\Dynamic\Category $mapping
         */
        $code = $args[0];
        if (!isset(self::$categoryMappings[$code])) {
            return false;
        }

        $mapping = self::$categoryMappings[$code];

        $categoryIds = array_diff($product->getCategoryIds(), [self::$rootCategoryId]);

        $result = [];
        foreach ($categoryIds as $categoryId) {
            if ($value = $mapping->getMappingValue($categoryId)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Dynamic attribute model
     *
     * @param Product $product
     * @param [] $args
     * @return string
     */
    public function getDynamic($product, $args)
    {
        /**
         * @var \Digidirect\Feed\Model\Dynamic\Attribute $attribute
         */
        $code = $args[0];
        if (!isset(self::$dynamicAttributes[$code])) {
            return false;
        }

        return self::$dynamicAttributes[$code]->getValue($product, $this);
    }

    /**
     * @param Product $object
     * @param string $key
     *
     * @return string
     */
    public function getData($object, $key)
    {
        $result = false;

        $product = $this->getProduct($object);

        $attribute = $this->getAttribute($key);

        if ($attribute && in_array($attribute->getFrontendInput(), ['select', 'multiselect'])) {
            if (is_scalar($product->getData($key))) {
                $value = $product->getResource()
                    ->getAttribute($key)
                    ->getSource()
                    ->getOptionText($product->getData($key));

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                $result = $value . '';
            }
        } else {
            $result = $product->getDataUsingMethod($key);

            if (!$result) {
                $result = $product->getData($key);
            }
        }

        if ($result && $attribute && $attribute->getFrontendInput() == 'media_image') {
            $result = $this->getImageUrl($result, $product);
        }

        return $result;
    }

    /**
     * Return product attribute model by attribute code
     *
     * @param string $code
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     */
    protected function getAttribute($code)
    {
        return isset(self::$attributes[$code]) ? self::$attributes[$code] : null;
    }

    /**
     * Load product model by object (from cache
     *
     * @param Product $object
     * @return Product
     */
    protected function getProduct($object)
    {
        if (!isset(self::$products[$object->getId()])) {
            //there could be a lot of products and we will have a problem with a memory limit
            self::$products = array_slice(self::$products, -100, null, true);
            $object->getResource()->load($object, $object->getId());
            self::$products[$object->getId()] = $object;
        }

        return self::$products[$object->getId()];
    }

    /**
     * @param Product $object
     * @return Product
     */
    protected function prepareObject($object)
    {
        return $this->getProduct($object);
    }

    /**
     * @param string $file
     * @param Product $product
     * @param string $imageId
     * @return string
     */
    protected function getImageUrl($file, $product = null, $imageId = 'product_page_main_image')
    {
        if (null === $product) {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            return self::$productImageBaseUrl . $file;
        }

        return $this->imageHelper->init($product, $imageId)
            ->setImageFile($file)
            ->getUrl();
    }

    /**
     * Get category for product
     *
     * @param Product $product
     * @return Category
     */
    public function getCategory($product)
    {
        $collection = $product->getCategoryCollection()
            ->setOrder(Category::KEY_LEVEL, Collection::SORT_ORDER_DESC)
            ->setPageSize(1);

        /** @var \Magento\Catalog\Model\Category $category */
        $category = $collection->getFirstItem();

        return $category;
    }

    /**
     * Get category collection for product (without root category)
     *
     * @param Product $product
     * @return array
     */
    public function getCategoryCollection($product)
    {
        $result = [];
        $collection = $product->getCategoryCollection();

        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($collection as $category) {
            if ($category->isInRootCategoryList()) {
                $result[] = $category;
            }
        }

        return $result;
    }
}
