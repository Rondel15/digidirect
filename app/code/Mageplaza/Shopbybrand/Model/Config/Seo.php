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
use Mageplaza\Shopbybrand\Api\Data\Config\SeoInterface;

/**
 * Class Seo
 * @package Mageplaza\Shopbybrand\Model\Config
 */
class Seo extends DataObject implements SeoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAddNoindexToPaginationPages()
    {
        return $this->getData(self::ADD_NOINDEX_TO_PAGINATION_PAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function setAddNoindexToPaginationPages($value)
    {
        $this->setData(self::ADD_NOINDEX_TO_PAGINATION_PAGES, $value);

        return $this;
    }
}
