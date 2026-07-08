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
 * @category  BSS
 * @package   Bss_PreOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Block;

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Helper\ProductData;
use Bss\PreOrder\Model\PreOrderAttribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Checkout\Model\SessionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class PreOrderProduct extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductData
     */
    protected $productData;

    /**
     * PreOrderProduct constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param SessionFactory $sessionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductData $productData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Data $helper,
        SessionFactory $sessionFactory,
        ProductCollectionFactory $productCollectionFactory,
        \Bss\PreOrder\Helper\ProductData $productData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->sessionFactory = $sessionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productData = $productData;
    }

    /**
     * Get Message Pre Order
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMessage()
    {
        $typeId = $this->getParentType();
        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();
        $message = $this->helper->replaceVariableX(
            $this->getProduct()->getData(PreOrderAttribute::PRE_ORDER_MESSAGE),
            $fromDate,
            $toDate
        );

        if ($typeId == Configurable::TYPE_CODE
            || ($typeId == ""
                && $this->getProduct()->getTypeId() == Configurable::TYPE_CODE)
        ) {
            return "";
        }

        if ($message == "") {
            $message = $this->helper->replaceVariableX(
                $this->helper->getMess(),
                $fromDate,
                $toDate
            );
        }

        return $message;
    }

    /**
     * Get Button Pre Order Html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonHtml()
    {
        $button = __("Pre-Order");
        if ($this->helper->getButton()) {
            $button = $this->helper->getButton();
        }

        return $button;
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFromDate()
    {
        return $this->helper->formatDate($this->getProductDetail()->getPreOrderFromDate());
    }

    /**
     * @return bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getToDate()
    {
        return $this->helper->formatDate($this->getProductDetail()->getPreOrderToDate());
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDetail()
    {
        return $this->helper->getProductById($this->getProduct()->getId());
    }

    /**
     * Check is Group Product
     *
     * @return bool
     */
    public function isGroupProduct()
    {
        $typeId = $this->getParentType();

        if ($typeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            return true;
        }

        return false;
    }

    /**
     * Get Notice Pre Order Mess
     *
     * @return array
     */
    public function getNote()
    {
        $getNote = $this->helper->getNote();
        if ($getNote !== null) {
            $note = explode(" ", $getNote);
        } else {
            $note = [];
        }

        $key = array_search("{date}", $note);
        $key2 = array_search("{preorder_date}", $note);

        if ($key !== false) {
            unset($note[$key]);
        }
        if ($key2 !== false) {
            unset($note[$key2]);
        }

        return $note;
    }

    /**
     * @return mixed|string
     */
    public function getAvailabilityMessage()
    {
        return $this->helper->getAvailabilityMessage($this->getProduct());
    }

    /**
     * Check message in cart with config mixin product
     *
     * @return false|\Magento\Framework\Phrase
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCartMessMixin()
    {
        $checkoutSession = $this->sessionFactory->create();
        $action = $checkoutSession->getAccessByUrl();
        $checkoutSession->setAccessByUrl(false);
        if (!$action && !$this->checkPreOrderProductInCart()) {
            return "We could not add both pre-order and regular items to an order.";
        }
        return '';
    }
    /**
     * Check pre-order product in cart
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkPreOrderProductInCart()
    {
        $allowMixin = $this->helper->isMix($this->helper->getStoreId());
        $hasPreOrderItem = false;
        $hasNormalItem = false;
        $quoteItems = $this->sessionFactory->create()->getQuote()->getItems();
        if (!$allowMixin && $quoteItems) {
            /** @var \Magento\Quote\Api\Data\CartItemInterface $item */
            foreach ($quoteItems as $item) {
                $check = $this->productData->checkPreOrderCartItem($item, $item->getQty());
                if ($check) {
                    $hasPreOrderItem = true;
                } else {
                    $hasNormalItem = true;
                }
            }
        }
        if ($hasPreOrderItem && $hasNormalItem) {
            return false;
        }
        return true;
    }
}
