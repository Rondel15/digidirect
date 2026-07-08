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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Products\Edit\Tab\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\DataObject;

/**
 * Class Type
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Products\Edit\Tab\Renderer
 */
class Type extends AbstractRenderer
{

    /**
     * @param DataObject $row
     *
     * @return array|mixed|string|null
     */
    public function render(DataObject $row)
    {
        $typeId = $row->getData($this->getColumn()->getIndex());
        switch ($typeId) {
            case ProductType::TYPE_SIMPLE:
                return __("Simple Product");
            case ProductType::TYPE_VIRTUAL:
                return __("Virtual Product");
            case ConfigurableType::TYPE_CODE:
                return __("Configurable Product");
            case DownloadableType::TYPE_DOWNLOADABLE:
                return __("Downloadable Product");

            default:
                return $typeId;
        }
    }
}
