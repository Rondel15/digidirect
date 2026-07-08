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

declare(strict_types=1);

namespace Mageplaza\Shopbybrand\Model\Export\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class EnableDisable
 * @package Mageplaza\Shopbybrand\Model\Export\Source
 */
class EnableDisable extends AbstractSource
{
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return [
            [
                'value' => '1',
                'label' => __('Enabled'),
            ],
            [
                'value' => '0',
                'label' => __('Disabled'),
            ],
        ];
    }
}
