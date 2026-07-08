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
namespace Bss\PreOrder\Observer;

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Helper\ProductData;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ObserverInterface;

class CheckBeforeUpdate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var bool
     */
    protected $hasPreOrderItem = false;

    /**
     * @var bool
     */
    protected $hasNormalItem = false;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * CheckBeforeUpdate constructor.
     * @param Data $helper
     * @param Configurable $configurable
     * @param ProductData $productData
     */
    public function __construct(
        Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Bss\PreOrder\Helper\ProductData $productData
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
        $this->productData = $productData;
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
        $quote = $observer->getCart()->getQuote();
        $infoDataObject = $observer->getInfo()->getData();
        $items = $quote->getAllItems();
        if ($this->helper->isEnable() && !$this->helper->isMix()) {
            foreach ($items as $item) {
                $product = $item;
                $qty = $item->getQty();
                if (isset($infoDataObject[$item->getId()])) {
                    $qty = $infoDataObject[$item->getId()]['qty'];
                }
                if ($this->productData->checkPreOrderCartItem($product, $qty)) {
                    $this->hasPreOrderItem = true;
                } else {
                    $this->hasNormalItem = true;
                }
            }
            if ($this->hasPreOrderItem && $this->hasNormalItem) {
                $this->hasPreOrderItem = false;
                $this->hasNormalItem = false;
                $message = "We could not add both pre-order and regular items to an order";
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
        }
    }
}
