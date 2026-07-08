<?php
namespace Digidirect\Digi\Helper;

use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Background
 * @package Digidirect\Digi\Helper
 */
class Background extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_BG_ENABLED = 'catalog/background_placeholders/enabled';
    const XML_PATH_BG_CATEGORY = 'catalog/background_placeholders';

    const ATTR_CODE_PART = 'image_background';

    const ENTITY_PRODUCT = 'product';
    const ENTITY_CATEGORY = 'category';

    const TYPE_DESKTOP = 'desktop';
    const TYPE_MOBILE = 'mobile';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Background constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Helper\Image $imageHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_imageHelper = $imageHelper;
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_BG_ENABLED);
    }

    /**
     * @param string $type
     * @param string $entityType
     * @return mixed
     */
    protected function getDefaultImageUrl($type = self::TYPE_DESKTOP, $entityType = self::ENTITY_CATEGORY)
    {
        $imageUrl = '';

        $imagePath = $this->scopeConfig->getValue(self::XML_PATH_BG_CATEGORY . '/' . $entityType . '_' . $type);
        if ($imagePath) {
            $folderName = 'catalog/' . $entityType . '/background';
            $path = $folderName . '/' . $imagePath;
            $imageUrl = $this->_urlBuilder
                    ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;
        }

        return $imageUrl;
    }

    /**
     * @param $attrCode
     * @param \Magento\Catalog\Model\Product|null $product
     * @return string
     */
    protected function getProductBackgroundUrl($attrCode, \Magento\Catalog\Model\Product $product = null)
    {
        if ($product
            && $product->getData($attrCode)
            && $product->getData($attrCode) !== 'no_selection'
        ) {
            return $this->_imageHelper
                ->init($product, $attrCode)
                ->setImageFile($product->getData($attrCode))
                ->getUrl();
        }

        return '';
    }

    /**
     * @param $attrCode
     * @param \Magento\Catalog\Model\Category|null $category
     * @return bool|string
     */
    protected function getCategoryBackgroundUrl($attrCode, \Magento\Catalog\Model\Category $category = null)
    {
        $bgIMage = '';
        if ($category) {
            try {
                $bgIMage = $category->getImageUrl($attrCode);
            } catch (LocalizedException $e) {
                //nothing
            }
        }

        return $bgIMage;
    }

    /**
     * Return current category object
     *
     * @return \Magento\Catalog\Model\Category|null
     */
    protected function getCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }

    /**
     * Retrieve current Product object
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    protected function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @param string $type
     * @param \Magento\Catalog\Model\Category|null $category
     * @return mixed|null
     */
    public function getCategoryBackground($type = self::TYPE_DESKTOP, \Magento\Catalog\Model\Category $category = null)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if (!$category) {
            $category = $this->getCategory();
        }

        $attrCode = self::ENTITY_CATEGORY . '_' . self::ATTR_CODE_PART . '_' . $type;
        $bgIMage = $this->getCategoryBackgroundUrl($attrCode, $category);
        return $bgIMage ?: $this->getDefaultImageUrl($type, self::ENTITY_CATEGORY);
    }

    /**
     * @param string $type
     * @param \Magento\Catalog\Model\Product|null $product
     * @return string|null
     */
    public function getProductBackground($type = self::TYPE_DESKTOP, \Magento\Catalog\Model\Product $product = null)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        if (!$product) {
            $product = $this->getProduct();
        }

        $attrCode = self::ENTITY_PRODUCT . '_' . self::ATTR_CODE_PART . '_' . $type;
        $bgIMage = $this->getProductBackgroundUrl($attrCode, $product);
        return $bgIMage ?: $this->getDefaultImageUrl($type, self::ENTITY_PRODUCT);
    }
}
