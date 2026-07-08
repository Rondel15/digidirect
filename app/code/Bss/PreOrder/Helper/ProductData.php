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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Helper;

use Bss\PreOrder\Model\Attribute\Source\Order;
use Bss\PreOrder\Model\PreOrderAttribute;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductData extends \Magento\Framework\Url\Helper\Data
{
    /**
     * @var Configurable
     */
    protected $typeConfigurable;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Configurable
     */
    protected $configurable;

    /**
     * @param Context $context
     * @param Configurable $typeConfigurable
     * @param Data $helper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Configurable $configurable
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Configurable $typeConfigurable,
        Data $helper,
        ProductCollectionFactory $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
    ) {
        $this->typeConfigurable = $typeConfigurable;
        $this->helper = $helper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->configurable = $configurable;
        parent::__construct($context);
    }

    /**
     * Get All Date Configurable Product and Child
     *
     * @param mixed $allowProduct
     * @return array
     * @throws
     */
    public function getAllData($allowProduct)
    {
        $result = [];
        if ($this->helper->isEnable()) {
            $hasButton = $this->helper->getButton();

            foreach ($allowProduct as $item) {
                $childProduct['stock_status'] = false;
                if ($item->getData('is_salable')) {
                    $childProduct['stock_status'] = true;
                }
                $childProduct['productId'] = $item->getData('entity_id');
                $childProduct[PreOrderAttribute::PRE_ORDER_STATUS] = $item->getData(PreOrderAttribute::PRE_ORDER_STATUS);
                $fromDate = $item->getData(PreOrderAttribute::PRE_ORDER_FROM_DATE);
                $toDate = $item->getData(PreOrderAttribute::PRE_ORDER_TO_DATE);
                $childProduct[PreOrderAttribute::PRE_ORDER_FROM_DATE] = $this->helper->formatDate($fromDate);
                $childProduct[PreOrderAttribute::PRE_ORDER_TO_DATE] = $this->helper->formatDate($toDate);
                $childProduct['availability_preorder'] = $this->helper->isAvailablePreOrderFromFlatData($fromDate, $toDate);
                $messageProduct = $item->getData(PreOrderAttribute::PRE_ORDER_MESSAGE);
                $childProduct[PreOrderAttribute::PRE_ORDER_AVAILABILITY_MESSAGE] = $this->helper->replaceVariableX(
                    $item->getData(PreOrderAttribute::PRE_ORDER_AVAILABILITY_MESSAGE),
                    $childProduct[PreOrderAttribute::PRE_ORDER_FROM_DATE],
                    $childProduct[PreOrderAttribute::PRE_ORDER_TO_DATE]
                );

                $messageProduct = $messageProduct !== null ? $messageProduct : '';
                $template_mess = !empty(trim($messageProduct)) ? $messageProduct : $this->helper->getMess();
                $childProduct[PreOrderAttribute::PRE_ORDER_MESSAGE] = $this->helper->replaceVariableX(
                    $template_mess,
                    $childProduct[PreOrderAttribute::PRE_ORDER_FROM_DATE],
                    $childProduct[PreOrderAttribute::PRE_ORDER_TO_DATE]
                );

                $button = __("Pre-Order");
                if ($hasButton) {
                    $button = $hasButton;
                }

                $childProduct['button'] = $button;

                $result['child'][$item->getData('entity_id')] = $childProduct;
            }
        }
        return $result;
    }

    /**
     * @param mixed $product
     * @param mixed $superAttribute
     * @return bool|\Magento\Catalog\Model\Product|null
     */
    public function getChildFromProductAttribute($product, $superAttribute)
    {
        $usedChild = $this->typeConfigurable->getProductByAttributes($superAttribute, $product);
        if ($usedChild) {
            return $usedChild;
        }
        return false;
    }

    /**
     * Check pre-order of all child product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isPreOrderForAllChild($product)
    {
        if ($product->getTypeId() == 'configurable') {
            $items = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($product->getTypeId() == 'grouped') {
            $items = $product->getTypeInstance()->getAssociatedProducts($product);
        } else {
            return false;
        }
        if (!empty($items)) {
            foreach ($items as $item) {
                $preOrder = $item->getData(PreOrderAttribute::PRE_ORDER_STATUS);
                $isInStock = $item->getData('is_salable');
                if ($preOrder == Order::ORDER_NO ||
                    ($preOrder == Order::ORDER_OUT_OF_STOCK && $isInStock) ||
                    $preOrder == Order::ORDER_YES && !$this->helper->isAvailablePreOrderFromFlatData(
                        $this->helper->formatDate($item->getData(PreOrderAttribute::PRE_ORDER_FROM_DATE)),
                        $this->helper->formatDate($item->getData(PreOrderAttribute::PRE_ORDER_TO_DATE))
                    )
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Get pre-order products
     *
     * @return \Magento\Framework\DataObject[]|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPreOrderProducts()
    {
        /**
         * @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
         */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->setStoreId($this->helper->getStoreId());
        $productCollection->addAttributeToFilter(PreOrderAttribute::PRE_ORDER_STATUS, ['neq' => 0]);
        if ($productCollection->getSize()) {
            return $productCollection->getItems();
        }
        return null;
    }

    /**
     * Check pre-order product in cart
     *
     * @param mixed $item
     * @param float $qty
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkPreOrderCartItem($item, $qty)
    {
        $productId = $item->getProduct()->getId();
        if ($item->getProduct()->getTypeId() == 'configurable') {
            $requestInfo =$item->getBuyRequest();
            $product = $this->helper->getProductById($productId);
            $product = $this->configurable->getProductByAttributes(
                $requestInfo['super_attribute'],
                $product
            );
            $productId = $product->getId();
        }
        $preOrderCart = $this->helper->getPreOrder($productId);
        $inStockCart = $this->helper->getIsInStock($productId);
        $availabilityPreOrder = $this->helper->isAvailablePreOrder($productId);
        $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart, $availabilityPreOrder);
        if ($inStockCart && $preOrderCart == 2) {
            $qtyProduct = $this->helper->getProductSalableQty($item, $productId);
            if ($qty > $qtyProduct) {
                $isPreOrderCart = true;
            }
        }
        return $isPreOrderCart;
    }
}
