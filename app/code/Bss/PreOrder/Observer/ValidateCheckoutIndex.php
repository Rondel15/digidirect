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
namespace Bss\PreOrder\Observer;

use Bss\PreOrder\Block\PreOrderProduct;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\ObserverInterface;

class ValidateCheckoutIndex implements ObserverInterface
{
    /**
     * @var PreOrderProduct
     */
    protected $preOrderProduct;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * CheckBeforeUpdate constructor.
     *
     * @param PreOrderProduct $preOrderProduct
     * @param RedirectInterface $redirect
     */
    public function __construct(
        \Bss\PreOrder\Block\PreOrderProduct $preOrderProduct,
        RedirectInterface $redirect
    ) {
        $this->preOrderProduct = $preOrderProduct;
        $this->redirect = $redirect;
    }

    /**
     * Validate When Update Cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->preOrderProduct->checkPreOrderProductInCart()) {
            $controller = $observer->getControllerAction();
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart', ['_secure' => true]);
        }
    }
}
