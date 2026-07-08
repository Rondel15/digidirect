<?php

namespace Digidirect\CheckoutFields\Plugin\Model\Order;

use Digidirect\CheckoutFields\Api\OrderFieldValueRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class RepositoryPlugin
 *
 * @package Digidirect\CheckoutFields\Plugin\Model\Order
 */
class RepositoryPlugin
{
    /**
     * @var OrderFieldValueRepositoryInterface
     */
    protected $orderFieldValueRepository;

    /**
     * @var OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    /**
     * RepositoryPlugin constructor.
     *
     * @param OrderExtensionFactory              $orderExtensionFactory
     * @param OrderFieldValueRepositoryInterface $orderFieldValueRepository
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        OrderFieldValueRepositoryInterface $orderFieldValueRepository
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderFieldValueRepository = $orderFieldValueRepository;
    }

    /**
     * @param OrderRepositoryInterface                           $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResult
    ) {
        foreach ($searchResult->getItems() as $product) {
            $this->addCheckoutFields($product);
        }

        return $searchResult;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface           $order
     *
     * @return OrderInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        $this->addCheckoutFields($order);

        return $order;
    }

    /**
     * @param ProductInterface $order
     *
     * @return self
     */
    protected function addCheckoutFields(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();

        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }
        $checkoutFields = $this->orderFieldValueRepository->getListByOrderId($order->getEntityId());
        $extensionAttributes->setOrderFieldValues($checkoutFields);
        $order->setExtensionAttributes($extensionAttributes);

        return $this;
    }
}
