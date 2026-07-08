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
 * @copyright  Copyright (c) 2018-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Block;

use Bss\PreOrder\Block\PreOrderProduct;

class Cart
{
    /**
     * @var PreOrderProduct
     */
    protected $preOrderProduct;

    /**
     * @param PreOrderProduct $preOrderProduct
     */
    public function __construct(
        \Bss\PreOrder\Block\PreOrderProduct $preOrderProduct
    ) {
        $this->preOrderProduct = $preOrderProduct;
    }

    /**
     * @param \Magento\Checkout\Block\Cart $subject
     * @param bool $result
     * @return mixed|true
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterHasError($subject, $result)
    {
        if (!$this->preOrderProduct->checkPreOrderProductInCart()) {
            return true;
        }
        return $result;
    }
}
