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
use Mageplaza\Shopbybrand\Api\Data\Config\SidebarInterface;

/**
 * Class Sidebar
 * @package Mageplaza\Shopbybrand\Model\Config
 */
class Sidebar extends DataObject implements SidebarInterface
{
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
    public function getFeaturedBrandShowTitle()
    {
        return $this->getData(self::FEATURED_BRAND_SHOW_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setFeaturedBrandShowTitle($value)
    {
        $this->setData(self::FEATURED_BRAND_SHOW_TITLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandThumbnail()
    {
        return $this->getData(self::SHOW_BRAND_THUMBNAIL);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandThumbnail($value)
    {
        $this->setData(self::SHOW_BRAND_THUMBNAIL, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandThumbnailTitle()
    {
        return $this->getData(self::BRAND_THUMBNAIL_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandThumbnailTitle($value)
    {
        $this->setData(self::BRAND_THUMBNAIL_TITLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandsQtyLimit()
    {
        return $this->getData(self::BRANDS_QTY_LIMIT);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandsQtyLimit($value)
    {
        $this->setData(self::BRANDS_QTY_LIMIT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowCategoryBrand()
    {
        return $this->getData(self::SHOW_CATEGORY_BRAND);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowCategoryBrand($value)
    {
        $this->setData(self::SHOW_CATEGORY_BRAND, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryBrandTitle()
    {
        return $this->getData(self::CATEGORY_BRAND_TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryBrandTitle($value)
    {
        $this->setData(self::CATEGORY_BRAND_TITLE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryQtyLimit()
    {
        return $this->getData(self::CATEGORY_QTY_LIMIT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryQtyLimit($value)
    {
        $this->setData(self::CATEGORY_QTY_LIMIT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandCategoryQuantity()
    {
        return $this->getData(self::SHOW_BRAND_CATEGORY_QUANTITY);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandCategoryQuantity($value)
    {
        $this->setData(self::SHOW_BRAND_CATEGORY_QUANTITY, $value);

        return $this;
    }
}
