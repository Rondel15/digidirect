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
use Mageplaza\Shopbybrand\Api\Data\Config\BrandInfoInterface;

/**
 * Class BrandInfo
 * @package Mageplaza\Shopbybrand\Model\Config
 */
class BrandInfo extends DataObject implements BrandInfoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultImage()
    {
        return $this->getData(self::DEFAULT_IMAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultImage($value)
    {
        $this->setData(self::DEFAULT_IMAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultBlock()
    {
        return $this->getData(self::DEFAULT_BLOCK);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultBlock($value)
    {
        $this->setData(self::DEFAULT_BLOCK, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandImageOnBrandPage()
    {
        return $this->getData(self::SHOW_BRAND_IMAGE_ON_BRAND_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandImageOnBrandPage($value)
    {
        $this->setData(self::SHOW_BRAND_IMAGE_ON_BRAND_PAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandDescriptionOnBrandPage()
    {
        return $this->getData(self::SHOW_BRAND_DESCRIPTION_ON_BRAND_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandDescriptionOnBrandPage($value)
    {
        $this->setData(self::SHOW_BRAND_DESCRIPTION_ON_BRAND_PAGE, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getShowBrandStaticBlockOnBrandPage()
    {
        return $this->getData(self::SHOW_BRAND_STATIC_BLOCK_ON_BRAND_PAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setShowBrandStaticBlockOnBrandPage($value)
    {
        $this->setData(self::SHOW_BRAND_STATIC_BLOCK_ON_BRAND_PAGE, $value);

        return $this;
    }
}
