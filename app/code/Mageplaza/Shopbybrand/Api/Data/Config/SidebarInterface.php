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
 * Interface BrandInfoInterface
 * @package Mageplaza\Shopbybrand\Api\Data\Config
 */
interface SidebarInterface
{
    const SHOW_FEATURED_BRANDS         = 'show_featured_brands';
    const FEATURED_BRAND_TITLE         = 'featured_brand_title';
    const FEATURED_BRAND_SHOW_TITLE    = 'featured_brand_show_title';
    const SHOW_BRAND_THUMBNAIL         = 'show_brand_thumbnail';
    const BRAND_THUMBNAIL_TITLE        = 'brand_thumbnail_title';
    const BRANDS_QTY_LIMIT             = 'brands_qty_limit';
    const SHOW_CATEGORY_BRAND          = 'show_category_brand';
    const CATEGORY_BRAND_TITLE         = 'category_brand_title';
    const CATEGORY_QTY_LIMIT           = 'category_qty_limit';
    const SHOW_BRAND_CATEGORY_QUANTITY = 'show_brand_category_quantity';

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
    public function getFeaturedBrandShowTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFeaturedBrandShowTitle($value);

    /**
     * @return string|null
     */
    public function getShowBrandThumbnail();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandThumbnail($value);

    /**
     * @return string|null
     */
    public function getBrandThumbnailTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandThumbnailTitle($value);

    /**
     * @return string|null
     */
    public function getBrandsQtyLimit();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBrandsQtyLimit($value);

    /**
     * @return string|null
     */
    public function getShowCategoryBrand();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowCategoryBrand($value);

    /**
     * @return string|null
     */
    public function getCategoryBrandTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryBrandTitle($value);

    /**
     * @return string|null
     */
    public function getCategoryQtyLimit();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryQtyLimit($value);

    /**
     * @return string|null
     */
    public function getShowBrandCategoryQuantity();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandCategoryQuantity($value);
}
