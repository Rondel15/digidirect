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
interface BrandInfoInterface
{
    const DEFAULT_IMAGE                         = 'default_image';
    const DEFAULT_BLOCK                         = 'default_block';
    const SHOW_BRAND_IMAGE_ON_BRAND_PAGE        = 'show_brand_image_on_brand_page';
    const SHOW_BRAND_DESCRIPTION_ON_BRAND_PAGE  = 'show_brand_description_on_brand_page';
    const SHOW_BRAND_STATIC_BLOCK_ON_BRAND_PAGE = 'show_brand_static_block_on_brand_page';

    /**
     * @return string|null
     */
    public function getDefaultImage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDefaultImage($value);

    /**
     * @return string|null
     */
    public function getDefaultBlock();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDefaultBlock($value);

    /**
     * @return string|null
     */
    public function getShowBrandImageOnBrandPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandImageOnBrandPage($value);

    /**
     * @return string|null
     */
    public function getShowBrandDescriptionOnBrandPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandDescriptionOnBrandPage($value);

    /**
     * @return string|null
     */
    public function getShowBrandStaticBlockOnBrandPage();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowBrandStaticBlockOnBrandPage($value);
}
