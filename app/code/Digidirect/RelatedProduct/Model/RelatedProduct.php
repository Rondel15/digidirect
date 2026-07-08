<?php
namespace Digidirect\RelatedProduct\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Catalog\Model\Indexer;
use Magento\Catalog\Model\ProductLink;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\EntryConverterPool;
use Magento\Framework\DataObject;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Class Product
 * @package Digidirect\RelatedProduct\Model
 */
class RelatedProduct extends \Magento\Catalog\Model\Product
{
    /**
     * @var array
     */
    protected $_productPositions;

    /**
     * @var array
     */
    protected $_categoryIds;

    /**
     * @var array
     */
    protected $_featuredImages;

    /**
     * @var array
     */
    protected $_relatedProductAttributes;

    /**
     * @var \Digidirect\RelatedProduct\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $_productOutputHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_currencyHelper;

    /**
     * RelatedProduct constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataService
     * @param Product\Url $url
     * @param Product\Link $productLink
     * @param Product\Configuration\Item\OptionFactory $itemOptionFactory
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param Product\OptionFactory $catalogProductOptionFactory
     * @param Product\Visibility $catalogProductVisibility
     * @param Product\Attribute\Source\Status $catalogProductStatus
     * @param Product\Media\Config $catalogProductMediaConfig
     * @param Product\Type $catalogProductType
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param ResourceModel\Product $resource
     * @param ResourceModel\Product\Collection $resourceCollection
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param Indexer\Product\Eav\Processor $productEavIndexerProcessor
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Product\Image\CacheFactory $imageCacheFactory
     * @param ProductLink\CollectionProvider $entityCollectionProvider
     * @param Product\LinkTypeProvider $linkTypeProvider
     * @param \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory
     * @param \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory
     * @param EntryConverterPool $mediaGalleryEntryConverterPool
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param \Digidirect\RelatedProduct\Helper\Data $helper
     * @param \Magento\Catalog\Helper\Output $productOutputHelper
     * @param \Magento\Framework\Pricing\Helper\Data $currencyHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $metadataService,
        Product\Url $url,
        Product\Link $productLink,
        Product\Configuration\Item\OptionFactory $itemOptionFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        Product\OptionFactory $catalogProductOptionFactory,
        Product\Visibility $catalogProductVisibility,
        Product\Attribute\Source\Status $catalogProductStatus,
        Product\Media\Config $catalogProductMediaConfig,
        Product\Type $catalogProductType,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Helper\Product $catalogProduct,
        ResourceModel\Product $resource,
        ResourceModel\Product\Collection $resourceCollection,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        Indexer\Product\Eav\Processor $productEavIndexerProcessor,
        CategoryRepositoryInterface $categoryRepository,
        Product\Image\CacheFactory $imageCacheFactory,
        ProductLink\CollectionProvider $entityCollectionProvider,
        Product\LinkTypeProvider $linkTypeProvider,
        \Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory,
        \Magento\Catalog\Api\Data\ProductLinkExtensionFactory $productLinkExtensionFactory,
        EntryConverterPool $mediaGalleryEntryConverterPool,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        \Digidirect\RelatedProduct\Helper\Data $helper,
        \Magento\Catalog\Helper\Output $productOutputHelper,
        \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $metadataService,
            $url,
            $productLink,
            $itemOptionFactory,
            $stockItemFactory,
            $catalogProductOptionFactory,
            $catalogProductVisibility,
            $catalogProductStatus,
            $catalogProductMediaConfig,
            $catalogProductType,
            $moduleManager,
            $catalogProduct,
            $resource,
            $resourceCollection,
            $collectionFactory,
            $filesystem,
            $indexerRegistry,
            $productFlatIndexerProcessor,
            $productPriceIndexerProcessor,
            $productEavIndexerProcessor,
            $categoryRepository,
            $imageCacheFactory,
            $entityCollectionProvider,
            $linkTypeProvider,
            $productLinkFactory,
            $productLinkExtensionFactory,
            $mediaGalleryEntryConverterPool,
            $dataObjectHelper,
            $joinProcessor,
            $data
        );
        $this->_helper = $helper;
        $this->_productOutputHelper = $productOutputHelper;
        $this->_currencyHelper = $currencyHelper;
    }

    /**
     * Retrieve only featured images
     *
     * @param string $imageType
     * @return array
     */
    public function getRelatedProductFeaturedImages($imageType)
    {
        if ($this->_featuredImages === null) {
            $this->_featuredImages = [];

            $images = $this->_helper->getGalleryImages($this, $imageType);
            foreach ($images as $image) {
                if ($image->getFeaturedProductImage()) {
                    array_push($this->_featuredImages, $image);
                }
            }
        }

        return $this->_featuredImages;
    }

    /**
     * Retrieve array with related product's attributes
     *
     * @return array
     */
    public function getRelatedProductAttributes()
    {
        if ($this->_relatedProductAttributes === null) {
            $this->_relatedProductAttributes = [];
            if (!empty($this->_helper->getShowingAttributes())) {
                $attributes = $this->getAttributes();
                foreach ($this->_helper->getShowingAttributes() as $code) {
                    if ($this->hasProductAttribute($code)) {
                        $attribute = $this->_getProductAttribute($attributes, $code);
                        if ($attribute !== null && $attribute->getValue() && $attribute->getValue() != ' ') {
                            $this->_relatedProductAttributes[$code] = $attribute;
                        }
                    }
                }
                $this->_relatedProductAttributes = array_filter($this->_relatedProductAttributes);
            }
        }
        return $this->_relatedProductAttributes;
    }

    /**
     * @param array $attributes
     * @param string $code
     * @return DataObject
     */
    protected function _getProductAttribute($attributes, $code)
    {
        $typeInstance = $this->getTypeInstance();

        $attribute = null;

        $attributeValue = [];

        if ($typeInstance instanceof Configurable
            && $attributes[$code]->getFrontendInput() === 'select') {
            $attribute = $typeInstance->getConfigurableAttributeCollection($this)
                ->addFieldToSelect('attribute_id')
                ->addFieldToFilter('attribute_id', $attributes[$code]->getId())
                ->getFirstItem();

            $attributeValue = $this->_helper->getValuesFromOptions($attribute->getOptions());
        }
        if (empty($attributeValue)) {
            $attributeValue = $this->_productOutputHelper->productAttribute(
                $this,
                $attributes[$code]->getFrontend()->getValue($this),
                $code
            );
            if ($this->isPrice($code)) {
                $attributeValue = $this->_currencyHelper->currencyByStore($attributeValue, true, true);
            }
        }

        if ($attributeValue !== null && $attributeValue !== '') {
            $attribute = new DataObject([
                'label' => $attributes[$code]->getFrontend()->getLabel(),
                'value' => $attributeValue
            ]);
        }

        return $attribute;
    }

    /**
     * Check has product attribute
     *
     * @param string $code
     * @return bool
     */
    public function hasProductAttribute($code)
    {
        $attributes = $this->getAttributes();
        return isset($attributes[$code]);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isPrice($code)
    {
        $prices = [
            ProductAttributeInterface::CODE_PRICE,
            ProductAttributeInterface::CODE_SPECIAL_PRICE,
            ProductAttributeInterface::CODE_TIER_PRICE,
            'minimal_price'
        ];
        if (in_array($code, $prices)) {
            return true;
        }
        return false;
    }
}
