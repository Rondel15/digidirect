<?php
namespace Digidirect\RelatedProduct\Helper;

use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_FILTER_TAB_ENABLE = 'digidirect_related_product/shown_parameters/filter_tab_enable';
    const XML_PATH_SHOWING_ATTRIBUTES = 'digidirect_related_product/shown_parameters/attributes';
    const XML_PATH_SHOWING_CATEGORY_ATTRIBUTES = 'digidirect_related_product/shown_parameters/category_attributes';
    const XML_PATH_FEATURED_IMAGES_ENABLE = 'digidirect_related_product/shown_parameters/images_enable';
    const XML_PATH_VISIBILITY_PRODUCTS_TYPE = 'digidirect_related_product/shown_parameters/visibility_products';
    const XML_PATH_DISPLAY_ONE_CATEGORY = 'digidirect_related_product/shown_parameters/display_one_category';

    /**
     * @var GalleryReadHandler
     */
    protected $galleryReadHandler;

    /**
     * Catalog Image Helper
     *
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Data constructor.
     * @param GalleryReadHandler $galleryReadHandler
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        GalleryReadHandler $galleryReadHandler,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->imageHelper = $imageHelper;
        $this->galleryReadHandler = $galleryReadHandler;
        parent::__construct($context);
    }

    /**
     *  Add image gallery to $product
     *
     * @param Product $product
     * @return void
     */
    public function addGallery(Product $product)
    {
        $this->galleryReadHandler->execute($product);
    }

    /**
     * @param ProductInterface|Product $product
     * @param string $imageType
     * @return mixed
     */
    public function getGalleryImages(ProductInterface $product, $imageType = 'product_page_image_medium')
    {
        $this->addGallery($product);
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /** @var $image \Magento\Catalog\Model\Product\Image */
                $image->setData(
                    $imageType,
                    $this->imageHelper->init($product, $imageType)
                        ->constrainOnly(true)
                        ->keepAspectRatio(true)
                        ->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        return $images;
    }

    /**
     * @return bool
     */
    public function isFilterTabEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FILTER_TAB_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isFeaturedImagesEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_FEATURED_IMAGES_ENABLE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return array
     */
    public function getVisibilityProductsTypes()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_VISIBILITY_PRODUCTS_TYPE);
    }

    /**
     * @return array
     */
    public function getShowingAttributes()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_SHOWING_ATTRIBUTES);
    }

    /**
     * @return array
     */
    public function getShowingCategoryAttributes()
    {
        return $this->getMultiSelectConfig(self::XML_PATH_SHOWING_CATEGORY_ATTRIBUTES);
    }

    /**
     * @return bool
     */
    public function displayOneCategory()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DISPLAY_ONE_CATEGORY,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Format options to values for frontend
     *
     * @param array $options
     * @return string
     */
    public function getValuesFromOptions($options)
    {
        $values = [];

        if ($options !== null) {
            foreach ($options as $option) {
                array_push($values, $option['label']);
            }
        }

        return $values;
    }

    /**
     * @param string $node
     * @return array
     */
    protected function getMultiSelectConfig($node)
    {
        return array_filter(
            explode(
                ',',
                $this->scopeConfig->getValue($node, ScopeInterface::SCOPE_STORE)
            )
        );
    }
}
