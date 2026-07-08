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

namespace Mageplaza\Shopbybrand\Model;

use Magento\Framework\DataObject;
use Mageplaza\Shopbybrand\Api\Data\BrandConfigInterface;

/**
 * Class BrandConfig
 * @package Mageplaza\Shopbybrand\Model
 */
class BrandConfig extends DataObject implements BrandConfigInterface
{
    /**
     * {@inheritdoc}
     */
    public function getGeneral()
    {
        return $this->getData(self::GENERAL);
    }

    /**
     * {@inheritdoc}
     */
    public function setGeneral($value)
    {
        $this->setData(self::GENERAL, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandsPageSettings()
    {
        return $this->getData(self::BRANDS_PAGE_SETTINGS);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandsPageSettings($value)
    {
        $this->setData(self::BRANDS_PAGE_SETTINGS, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBrandInfo()
    {
        return $this->getData(self::BRAND_INFO);
    }

    /**
     * {@inheritdoc}
     */
    public function setBrandInfo($value)
    {
        $this->setData(self::BRAND_INFO, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSidebar()
    {
        return $this->getData(self::SIDEBAR);
    }

    /**
     * {@inheritdoc}
     */
    public function setSidebar($value)
    {
        $this->setData(self::SIDEBAR, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSeo()
    {
        return $this->getData(self::BRAND_SEO);
    }

    /**
     * {@inheritdoc}
     */
    public function setSeo($value)
    {
        $this->setData(self::BRAND_SEO, $value);

        return $this;
    }
}
