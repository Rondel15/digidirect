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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Checkout\Controller\Cart;

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Helper\ProductData;
use Magento\Checkout\Model\Cart;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;

class UpdateItemOptions
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var bool
     */
    protected $hasPreOrderItem = false;

    /**
     * @var bool
     */
    protected $hasNormalItem = false;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * Check Before Update constructor.
     *
     * @param Data $helper
     * @param ProductData $productData
     * @param Cart $cart
     * @param Configurable $configurable
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Bss\PreOrder\Helper\ProductData $productData,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->helper = $helper;
        $this->cart = $cart;
        $this->configurable = $configurable;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->productData = $productData;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\UpdateItemOptions $subject
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeExecute($subject)
    {
        $id = (int)$subject->getRequest()->getParam('id');
        $params = $subject->getRequest()->getParams();
        if (isset($params['qty']) && $this->helper->isEnable() && !$this->helper->isMix()) {
            $quoteItems = $this->cart->getQuote()->getAllItems();
            $defaultQty = $params['qty'];
            foreach ($quoteItems as $item) {
                $productId = $item->getProduct()->getId();
                $product = $item;
                $qty = $item->getQty();
                if ($id == $item->getId()) {
                    $defaultQty = $qty;
                    $qty = $params['qty'];
                }
                if ($this->productData->checkPreOrderCartItem($product, $qty)) {
                    $this->hasPreOrderItem = true;
                } else {
                    $this->hasNormalItem = true;
                }
            }
            $this->checkShowError($subject, $defaultQty);
        }
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\UpdateItemOptions $subject
     * @param float $qty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkShowError($subject, $qty)
    {
        try {
            if ($this->hasPreOrderItem && $this->hasNormalItem) {
                $this->hasPreOrderItem = false;
                $this->hasNormalItem = false;
                $message = "We could not add both pre-order and regular items to an order";
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
        } catch (\Exception $e) {
            $subject->getRequest()->setParam('qty', $qty);
            $this->messageManager->addErrorMessage($e->getMessage());
            return null;
        }
    }
}
