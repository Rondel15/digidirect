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
 * Interface GeneralInterface
 * @package Mageplaza\Shopbybrand\Api\Data\Config
 */
interface GeneralInterface
{
    const IS_ENABLED                              = 'is_enabled';
    const BRAND_ATTRIBUTE                         = 'brand_attribute';
    const BRAND_ROUTE                             = 'brand_route';
    const BRAND_LINK_TITLE                        = 'brand_link_title';
    const SHOW_BRAND_LINK_IN                      = 'show_brand_link_in';
    const SHOW_BRAND_IN_CATEGORY_MENU             = 'show_brand_in_category_menu';
    const WHAT_TO_SHOW                            = 'what_to_show';
    const BRAND_MENU_GRID_LAYOUT                  = 'brand_menu_grid_layout';
    const MAXIMUM_BRANDS_TO_SHOW                  = 'maximum_brands_to_show';
    const SHOW_BRANDS_WITHOUT_PRODUCTS_ON_MENU    = 'show_brands_without_products_on_menu';
    const SHOW_BRAND_INFO_ON_PRODUCT_LISTING_PAGE = 'show_brand_info_on_product_listing_page';
    const SHOW_BRAND_INFO_IN_PRODUCT_PAGE         = 'show_brand_info_in_product_page';
    const SHOW_BRAND_INFO_IN_PRODUCT_ADMIN_GRID   = 'show_brand_info_in_product_admin_grid';
    const BRAND_LOGO_WIDTH_IN_PRODUCT_PAGE        = 'brand_logo_width_in_product_page';
    const BRAND_LOGO_HEIGHT_IN_PRODUCT_PAGE       = 'brand_logo_height_in_product_page';

    /**
     * @return string|null
     */
    public function getIsEnabled();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIsEnabled($value);

    /**
     * @return string|null
     */
    public function getBrandAttribute();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandAttribute($value);

    /**
     * @return string|null
     */
    public function getBrandRoute();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandRoute($value);

    /**
     * @return string|null
     */
    public function getBrandLinkTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandLinkTitle($value);

    /**
     * @return string|null
     */
    public function getShowBrandLinkIn();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandLinkIn($value);

    /**
     * @return string|null
     */
    public function getShowBrandInCategoryMenu();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandInCategoryMenu($value);

    /**
     * @return string|null
     */
    public function getWhatToShow();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setWhatToShow($value);

    /**
     * @return string|null
     */
    public function getBrandMenuGridLayout();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandMenuGridLayout($value);

    /**
     * @return string|null
     */
    public function getMaximumBrandsToShow();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMaximumBrandsToShow($value);

    /**
     * @return string|null
     */
    public function getShowBrandsWithoutProductsOnMenu();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandsWithoutProductsOnMenu($value);

    /**
     * @return string|null
     */
    public function getShowBrandInfoOnProductListingPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandInfoOnProductListingPage($value);

    /**
     * @return string|null
     */
    public function getShowBrandInfoInProductPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandInfoInProductPage($value);

    /**
     * @return string|null
     */
    public function getShowBrandInfoInProductAdminGrid();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandInfoInProductAdminGrid($value);

    /**
     * @return string|null
     */
    public function getBrandLogoWidthInProductPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandLogoWidthInProductPage($value);

    /**
     * @return string|null
     */
    public function getBrandLogoHeightInProductPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandLogoHeightInProductPage($value);
}
