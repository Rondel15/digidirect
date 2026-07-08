<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\PreOrder\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class ConvertAttribute implements DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Update attribute
     *
     * @return ConvertAttribute|void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        if ($eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'pre_oder_from_date', 'attribute_code')) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_oder_from_date',
                'attribute_code',
                'pre_order_from_date'
            );
        }

        if ($eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'pre_oder_to_date', 'attribute_code')) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'pre_oder_to_date',
                'attribute_code',
                'pre_order_to_date'
            );
        }

        if ($eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'preorder', 'attribute_code')) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'preorder',
                'attribute_code',
                'pre_order_status'
            );
        }

        if ($eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'message', 'attribute_code')) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'message',
                'attribute_code',
                'pre_order_message'
            );
        }

        if ($eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'availability_message', 'attribute_code')) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'availability_message',
                'attribute_code',
                'pre_order_availability_message'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.2.3';
    }
}
