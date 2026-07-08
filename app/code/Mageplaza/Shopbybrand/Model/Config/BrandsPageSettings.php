<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model\Config;

use Magento\Framework\DataObject;
use Mageplaza\Shopbybrand\Api\Data\Config\BrandsPageSettingsInterface;

/**
 * Class BrandsPageSettings
 * @package Mageplaza\Shopbybrand\Model\Config
 */
class BrandsPageSettings extends DataObject implements BrandsPageSettingsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBrandListName()
    {
        return $this->getData(self::BRAND_LIST_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandListName($value)
    {
        $this->setData(self::BRAND_LIST_NAME, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStyleOfBrandListPage()
    {
        return $this->getData(self::STYLE_OF_BRAND_LIST_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setStyleOfBrandListPage($value)
    {
        $this->setData(self::STYLE_OF_BRAND_LIST_PAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayOption()
    {
        return $this->getData(self::DISPLAY_OPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayOption($value)
    {
        $this->setData(self::DISPLAY_OPTION, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandLogoWidth()
    {
        return $this->getData(self::BRAND_LOGO_WIDTH);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandLogoWidth($value)
    {
        $this->setData(self::BRAND_LOGO_WIDTH, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandLogoHeight()
    {
        return $this->getData(self::BRAND_LOGO_HEIGHT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandLogoHeight($value)
    {
        $this->setData(self::BRAND_LOGO_HEIGHT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStyleColor()
    {
        return $this->getData(self::STYLE_COLOR);
    }

    /**
     * {@inheritdoc}
     */
    public function setStyleColor($value)
    {
        $this->setData(self::STYLE_COLOR, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandShortDescription()
    {
        return $this->getData(self::SHOW_BRAND_SHORT_DESCRIPTION);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandShortDescription($value)
    {
        $this->setData(self::SHOW_BRAND_SHORT_DESCRIPTION, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandsWithoutProducts()
    {
        return $this->getData(self::SHOW_BRANDS_WITHOUT_PRODUCTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandsWithoutProducts($value)
    {
        $this->setData(self::SHOW_BRANDS_WITHOUT_PRODUCTS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandProductQuantity()
    {
        return $this->getData(self::SHOW_BRAND_PRODUCT_QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandProductQuantity($value)
    {
        $this->setData(self::SHOW_BRAND_PRODUCT_QUANTITY, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandQuickViewPopup()
    {
        return $this->getData(self::SHOW_BRAND_QUICK_VIEW_POPUP);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandQuickViewPopup($value)
    {
        $this->setData(self::SHOW_BRAND_QUICK_VIEW_POPUP, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomCss()
    {
        return $this->getData(self::CUSTOM_CSS);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomCss($value)
    {
        $this->setData(self::CUSTOM_CSS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandCategoriesFilter()
    {
        return $this->getData(self::SHOW_BRAND_CATEGORIES_FILTER);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandCategoriesFilter($value)
    {
        $this->setData(self::SHOW_BRAND_CATEGORIES_FILTER, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandAlphabetFilter()
    {
        return $this->getData(self::SHOW_BRAND_ALPHABET_FILTER);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandAlphabetFilter($value)
    {
        $this->setData(self::SHOW_BRAND_ALPHABET_FILTER, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandFilterAlphabet()
    {
        return $this->getData(self::BRAND_FILTER_ALPHABET);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandFilterAlphabet($value)
    {
        $this->setData(self::BRAND_FILTER_ALPHABET, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandFilterCharacterSet()
    {
        return $this->getData(self::BRAND_FILTER_CHARACTER_SET);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandFilterCharacterSet($value)
    {
        $this->setData(self::BRAND_FILTER_CHARACTER_SET, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandSearchBlock()
    {
        return $this->getData(self::SHOW_BRAND_SEARCH_BLOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandSearchBlock($value)
    {
        $this->setData(self::SHOW_BRAND_SEARCH_BLOCK, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandSearchMinChars()
    {
        return $this->getData(self::BRAND_SEARCH_MIN_CHARS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandSearchMinChars($value)
    {
        $this->setData(self::BRAND_SEARCH_MIN_CHARS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandSearchNumberOfSearchResult()
    {
        return $this->getData(self::BRAND_SEARCH_NUMBER_OF_SEARCH_RESULT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandSearchNumberOfSearchResult($value)
    {
        $this->setData(self::BRAND_SEARCH_NUMBER_OF_SEARCH_RESULT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandSearchShowThumbnailImage()
    {
        return $this->getData(self::BRAND_SEARCH_SHOW_THUMBNAIL_IMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandSearchShowThumbnailImage($value)
    {
        $this->setData(self::BRAND_SEARCH_SHOW_THUMBNAIL_IMAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowFeaturedBrands()
    {
        return $this->getData(self::SHOW_FEATURED_BRANDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowFeaturedBrands($value)
    {
        $this->setData(self::SHOW_FEATURED_BRANDS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFeaturedBrandTitle()
    {
        return $this->getData(self::FEATURED_BRAND_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFeaturedBrandTitle($value)
    {
        $this->setData(self::FEATURED_BRAND_TITLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayFeaturedBrandsStyle()
    {
        return $this->getData(self::DISPLAY_FEATURED_BRANDS_STYLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayFeaturedBrandsStyle($value)
    {
        $this->setData(self::DISPLAY_FEATURED_BRANDS_STYLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayInformationFeaturedBrands()
    {
        return $this->getData(self::DISPLAY_INFORMATION_FEATURED_BRANDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setDisplayInformationFeaturedBrands($value)
    {
        $this->setData(self::DISPLAY_INFORMATION_FEATURED_BRANDS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowRelatedProducts()
    {
        return $this->getData(self::SHOW_RELATED_PRODUCTS);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowRelatedProducts($value)
    {
        $this->setData(self::SHOW_RELATED_PRODUCTS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandRelatedTitle()
    {
        return $this->getData(self::BRAND_RELATED_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandRelatedTitle($value)
    {
        $this->setData(self::BRAND_RELATED_TITLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandRelatedLimit()
    {
        return $this->getData(self::BRAND_RELATED_LIMIT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandRelatedLimit($value)
    {
        $this->setData(self::BRAND_RELATED_LIMIT, $value);

        return $this;
    }
}
