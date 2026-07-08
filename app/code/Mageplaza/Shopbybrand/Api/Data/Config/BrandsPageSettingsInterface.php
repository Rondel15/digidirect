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

namespace Mageplaza\Shopbybrand\Api\Data\Config;

/**
 * Interface BrandsPageSettingsInterface
 * @package Mageplaza\Shopbybrand\Api\Data\Config
 */
interface BrandsPageSettingsInterface
{
    const BRAND_LIST_NAME                      = 'brand_list_name';
    const STYLE_OF_BRAND_LIST_PAGE             = 'style_of_brand_list_page';
    const DISPLAY_OPTION                       = 'display_option';
    const BRAND_LOGO_WIDTH                     = 'brand_logo_width';
    const BRAND_LOGO_HEIGHT                    = 'brand_logo_height';
    const STYLE_COLOR                          = 'style_color';
    const SHOW_BRAND_SHORT_DESCRIPTION         = 'show_brand_short_description';
    const SHOW_BRANDS_WITHOUT_PRODUCTS         = 'show_brands_without_products';
    const SHOW_BRAND_PRODUCT_QUANTITY          = 'show_brand_product_quantity';
    const SHOW_BRAND_QUICK_VIEW_POPUP          = 'show_brand_quick_view_popup';
    const CUSTOM_CSS                           = 'custom_css';
    const SHOW_BRAND_CATEGORIES_FILTER         = 'show_brand_categories_filter';
    const SHOW_BRAND_ALPHABET_FILTER           = 'show_brand_alphabet_filter';
    const BRAND_FILTER_ALPHABET                = 'brand_filter_alphabet';
    const BRAND_FILTER_CHARACTER_SET           = 'brand_filter_character_set';
    const SHOW_BRAND_SEARCH_BLOCK              = 'show_brand_search_block';
    const BRAND_SEARCH_MIN_CHARS               = 'brand_search_min_chars';
    const BRAND_SEARCH_NUMBER_OF_SEARCH_RESULT = 'brand_search_number_of_search_result';
    const BRAND_SEARCH_SHOW_THUMBNAIL_IMAGE    = 'brand_search_show_thumbnail_image';
    const SHOW_FEATURED_BRANDS                 = 'show_featured_brands';
    const FEATURED_BRAND_TITLE                 = 'featured_brand_title';
    const DISPLAY_FEATURED_BRANDS_STYLE        = 'display_featured_brands_style';
    const DISPLAY_INFORMATION_FEATURED_BRANDS  = 'display_information_featured_brands';
    const SHOW_RELATED_PRODUCTS                = 'show_related_products';
    const BRAND_RELATED_TITLE                  = 'brand_related_title';
    const BRAND_RELATED_LIMIT                  = 'brand_related_limit';

    /**
     * @return string|null
     */
    public function getBrandListName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandListName($value);

    /**
     * @return string|null
     */
    public function getStyleOfBrandListPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStyleOfBrandListPage($value);

    /**
     * @return string|null
     */
    public function getDisplayOption();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDisplayOption($value);

    /**
     * @return string|null
     */
    public function getBrandLogoWidth();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandLogoWidth($value);

    /**
     * @return string|null
     */
    public function getBrandLogoHeight();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandLogoHeight($value);

    /**
     * @return string|null
     */
    public function getStyleColor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setStyleColor($value);

    /**
     * @return string|null
     */
    public function getShowBrandShortDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandShortDescription($value);

    /**
     * @return string|null
     */
    public function getShowBrandsWithoutProducts();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandsWithoutProducts($value);

    /**
     * @return string|null
     */
    public function getShowBrandProductQuantity();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandProductQuantity($value);

    /**
     * @return string|null
     */
    public function getShowBrandQuickViewPopup();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandQuickViewPopup($value);

    /**
     * @return string|null
     */
    public function getCustomCss();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCustomCss($value);

    /**
     * @return string|null
     */
    public function getShowBrandCategoriesFilter();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandCategoriesFilter($value);

    /**
     * @return string|null
     */
    public function getShowBrandAlphabetFilter();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandAlphabetFilter($value);

    /**
     * @return string|null
     */
    public function getBrandFilterAlphabet();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandFilterAlphabet($value);

    /**
     * @return string|null
     */
    public function getBrandFilterCharacterSet();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandFilterCharacterSet($value);

    /**
     * @return string|null
     */
    public function getShowBrandSearchBlock();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandSearchBlock($value);

    /**
     * @return string|null
     */
    public function getBrandSearchMinChars();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandSearchMinChars($value);

    /**
     * @return string|null
     */
    public function getBrandSearchNumberOfSearchResult();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandSearchNumberOfSearchResult($value);

    /**
     * @return string|null
     */
    public function getBrandSearchShowThumbnailImage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandSearchShowThumbnailImage($value);

    /**
     * @return string|null
     */
    public function getShowFeaturedBrands();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowFeaturedBrands($value);

    /**
     * @return string|null
     */
    public function getFeaturedBrandTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFeaturedBrandTitle($value);

    /**
     * @return string|null
     */
    public function getDisplayFeaturedBrandsStyle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDisplayFeaturedBrandsStyle($value);

    /**
     * @return string|null
     */
    public function getDisplayInformationFeaturedBrands();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDisplayInformationFeaturedBrands($value);

    /**
     * @return string|null
     */
    public function getShowRelatedProducts();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowRelatedProducts($value);

    /**
     * @return string|null
     */
    public function getBrandRelatedTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandRelatedTitle($value);

    /**
     * @return string|null
     */
    public function getBrandRelatedLimit();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandRelatedLimit($value);
}
