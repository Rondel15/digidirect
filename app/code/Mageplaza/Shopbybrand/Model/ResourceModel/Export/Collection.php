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

namespace Mageplaza\Shopbybrand\Model\ResourceModel\Export;

/**
 * Class Collection
 * @package Mageplaza\Shopbybrand\Model\ResourceModel\Export
 */
class Collection extends \Magento\Framework\Data\Collection
{

    /**
     * @param $field
     * @param null $condition
     *
     * @return \Magento\Framework\Data\Collection|void
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $condition = $condition['like']->__toString();
        $condition = str_replace(["%", "\\", "'"], null, $condition);

        if ($field === 'attribute_code') {
            foreach ($this->getAllIds() as $value) {
                if (strpos($value, $condition) === false) {
                    $this->removeItemByKey($value);
                }
            }
        }
        if ($field === 'frontend_label') {

            foreach ($this->getItems() as $key => $item) {
                if (strpos($item->getData()['frontend_label'], $condition) === false) {
                    $this->removeItemByKey($key);
                }
            }
        }
    }
}
