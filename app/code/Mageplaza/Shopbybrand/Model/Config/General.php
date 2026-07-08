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
use Mageplaza\Shopbybrand\Api\Data\Config\GeneralInterface;

/**
 * Class General
 * @package Mageplaza\Shopbybrand\Model\Config
 */
class General extends DataObject implements GeneralInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIsEnabled()
    {
        return $this->getData(self::IS_ENABLED);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsEnabled($value)
    {
        $this->setData(self::IS_ENABLED, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandAttribute()
    {
        return $this->getData(self::BRAND_ATTRIBUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandAttribute($value)
    {
        $this->setData(self::BRAND_ATTRIBUTE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandRoute()
    {
        return $this->getData(self::BRAND_ROUTE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandRoute($value)
    {
        $this->setData(self::BRAND_ROUTE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandLinkTitle()
    {
        return $this->getData(self::BRAND_LINK_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandLinkTitle($value)
    {
        $this->setData(self::BRAND_LINK_TITLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandLinkIn()
    {
        return $this->getData(self::SHOW_BRAND_LINK_IN);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandLinkIn($value)
    {
        $this->setData(self::SHOW_BRAND_LINK_IN, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandInCategoryMenu()
    {
        return $this->getData(self::SHOW_BRAND_IN_CATEGORY_MENU);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandInCategoryMenu($value)
    {
        $this->setData(self::SHOW_BRAND_IN_CATEGORY_MENU, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhatToShow()
    {
        return $this->getData(self::WHAT_TO_SHOW);
    }

    /**
     * {@inheritdoc}
     */
    public function setWhatToShow($value)
    {
        $this->setData(self::WHAT_TO_SHOW, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandMenuGridLayout()
    {
        return $this->getData(self::BRAND_MENU_GRID_LAYOUT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandMenuGridLayout($value)
    {
        $this->setData(self::BRAND_MENU_GRID_LAYOUT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaximumBrandsToShow()
    {
        return $this->getData(self::MAXIMUM_BRANDS_TO_SHOW);
    }

    /**
     * {@inheritdoc}
     */
    public function setMaximumBrandsToShow($value)
    {
        $this->setData(self::MAXIMUM_BRANDS_TO_SHOW, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandsWithoutProductsOnMenu()
    {
        return $this->getData(self::SHOW_BRANDS_WITHOUT_PRODUCTS_ON_MENU);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandsWithoutProductsOnMenu($value)
    {
        $this->setData(self::SHOW_BRANDS_WITHOUT_PRODUCTS_ON_MENU, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandInfoOnProductListingPage()
    {
        return $this->getData(self::SHOW_BRAND_INFO_ON_PRODUCT_LISTING_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandInfoOnProductListingPage($value)
    {
        $this->setData(self::SHOW_BRAND_INFO_ON_PRODUCT_LISTING_PAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandInfoInProductPage()
    {
        return $this->getData(self::SHOW_BRAND_INFO_IN_PRODUCT_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandInfoInProductPage($value)
    {
        $this->setData(self::SHOW_BRAND_INFO_IN_PRODUCT_PAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandInfoInProductAdminGrid()
    {
        return $this->getData(self::SHOW_BRAND_INFO_IN_PRODUCT_ADMIN_GRID);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandInfoInProductAdminGrid($value)
    {
        $this->setData(self::SHOW_BRAND_INFO_IN_PRODUCT_ADMIN_GRID, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandLogoWidthInProductPage()
    {
        return $this->getData(self::BRAND_LOGO_WIDTH_IN_PRODUCT_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandLogoWidthInProductPage($value)
    {
        $this->setData(self::BRAND_LOGO_WIDTH_IN_PRODUCT_PAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandLogoHeightInProductPage()
    {
        return $this->getData(self::BRAND_LOGO_HEIGHT_IN_PRODUCT_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandLogoHeightInProductPage($value)
    {
        $this->setData(self::BRAND_LOGO_HEIGHT_IN_PRODUCT_PAGE, $value);

        return $this;
    }
}
