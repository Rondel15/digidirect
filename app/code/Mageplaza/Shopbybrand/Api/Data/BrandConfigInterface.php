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

namespace Mageplaza\Shopbybrand\Api\Data;

/**
 * Interface BrandConfigInterface
 * @package Mageplaza\Shopbybrand\Api\Data
 */
interface BrandConfigInterface
{
    const GENERAL              = 'general';
    const BRANDS_PAGE_SETTINGS = 'brandpage';
    const BRAND_INFO           = 'brandview';
    const SIDEBAR              = 'sidebar';
    const BRAND_SEO            = 'brand_seo';

    /**
     * @return \Mageplaza\Shopbybrand\Api\Data\Config\GeneralInterface
     */
    public function getGeneral();

    /**
     * @param \Mageplaza\Shopbybrand\Api\Data\Config\GeneralInterface $value
     *
     * @return $this
     */
    public function setGeneral($value);

    /**
     * @return \Mageplaza\Shopbybrand\Api\Data\Config\BrandsPageSettingsInterface
     */
    public function getBrandsPageSettings();

    /**
     * @param \Mageplaza\Shopbybrand\Api\Data\Config\BrandsPageSettingsInterface $value
     *
     * @return $this
     */
    public function setBrandsPageSettings($value);

    /**
     * @return \Mageplaza\Shopbybrand\Api\Data\Config\BrandInfoInterface
     */
    public function getBrandInfo();

    /**
     * @param \Mageplaza\Shopbybrand\Api\Data\Config\BrandInfoInterface $value
     *
     * @return $this
     */
    public function setBrandInfo($value);

    /**
     * @return \Mageplaza\Shopbybrand\Api\Data\Config\SidebarInterface
     */
    public function getSidebar();

    /**
     * @param \Mageplaza\Shopbybrand\Api\Data\Config\SidebarInterface $value
     *
     * @return $this
     */
    public function setSidebar($value);

    /**
     * @return \Mageplaza\Shopbybrand\Api\Data\Config\SeoInterface
     */
    public function getSeo();

    /**
     * @param \Mageplaza\Shopbybrand\Api\Data\Config\SeoInterface $value
     *
     * @return $this
     */
    public function setSeo($value);
}
