<?php

namespace Digidirect\Utilities\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Checkout\Model\Session;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;
use Magento\Sales\Api\OrderRepositoryInterface as OrderRepository;

class Attribute extends AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $attribute;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute[]
     */
    protected $attributes;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterfaceFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Data Constructor
     *
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        Session $session,
        EntityAttribute $attribute,
        ProductRepository $productRepository,
        OrderFactory $orderFactory,
        OrderRepository $orderRepository
    ) {
        $this->session = $session;
        $this->attribute = $attribute;
        $this->productRepository = $productRepository;
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;

        parent::__construct($context);
    }

    /**
     * Check has product attribute value or not
     *
     * @param string $attrCode
     * @param string $value
     * @return bool
     */
    public function hasCartProductAttributeValue($attrCode, $value)
    {
        $items = $this->session->getQuote()->getAllItems();
        return $this->checkItemsForAttributeValue($items, $attrCode, $value);
    }

    /**
     * Check has product attribute value or not
     *
     * @param string|int $orderId
     * @param string $attrCode
     * @param string $value
     * @param bool $useIncrement
     * @return bool
     */
    public function hasOrderProductAttributeValue($orderId, $attrCode, $value, $useIncrement = true)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if ($useIncrement) {
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);
            if (!$order->getId()) {
                return false;
            }
        } else {
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }

        $items = $order->getAllItems();
        return $this->checkItemsForAttributeValue($items, $attrCode, $value);
    }

    /**
     * Get product attribute value
     *
     * @param ModelProduct $product
     * @param string $attrCode
     * @return string|null
     */
    public function getProductAttributeValue(ModelProduct $product, $attrCode)
    {
        if (!$product->hasData($attrCode)) {
            try {
                $product = $this->productRepository->getById($product->getId(), false, $product->getStoreId());
            } catch (NoSuchEntityException $e) {
                return null;
            }
        }

        $value = $product->getData($attrCode);
        return $value;
    }

    /**
     * Load attribute data by code
     *
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getProductAttributeInfo($attributeCode)
    {
        if (!isset($this->attributes[$attributeCode])) {
            $this->attributes[$attributeCode] = $this->attribute->loadByCode(
                ModelProduct::ENTITY,
                $attributeCode
            );
        }
        return $this->attributes[$attributeCode];
    }

    /**
     * Check items for passing attribute value
     *
     * @param \Magento\Quote\Model\Quote\Item[]|\Magento\Catalog\Model\Product[]|\Magento\Sales\Model\Order\Item[]
     *              $items
     * @param string $attrCode
     * @param string|null $value
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkItemsForAttributeValue($items, $attrCode, $value = null)
    {
        if (!$items) {
            return false;
        }

        if (!$attrCode) {
            throw new LocalizedException(__('Wrong attribute code'));
        }

        $attribute = $this->getProductAttributeInfo($attrCode);
        if (!$attribute) {
            return false;
        }

        $isMultiselect = 'multiselect' == $attribute->getData(AttributeInterface::FRONTEND_INPUT);
        if ($isMultiselect && !is_array($value)) {
            $value = explode(',', $value);
        }

        foreach ($items as $item) {
            if (!$item instanceof ModelProduct) {
                if (method_exists($item, 'getProduct')) {
                    $item = $item->getProduct();
                } else {
                    throw new LocalizedException(__('Item does not contain a product'));
                }
            }

            $attrValue = $this->getProductAttributeValue($item, $attrCode);

            if ($value === null) {
                if ($attrValue) {
                    return true;
                }
                continue;
            }

            if ((!$isMultiselect && $attrValue == $value)
                || ($isMultiselect
                    && ($options = explode(',', $attrValue))
                    && !array_diff($value, $options))
            ) {
                return true;
            }
        }
        return false;
    }
}
