<?php
declare(strict_types=1);

namespace Digidirect\Digi\Helper;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Sales\Model\Order\Item;

/**
 * Class FaceBookPixel
 * @package Digidirect\Digi\Helper
 */
class FaceBookPixel extends \Magento\Framework\App\Helper\AbstractHelper
{

    const EXCLUDED_ID_CATEGORIES = [1,2];
    private $brandLabel = false;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * FaceBookPixel constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        CheckoutSession $checkoutSession,
        Json $jsonSerializer
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->jsonSerializer = $jsonSerializer;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param $filter
     * @param $activeLabel
     */
    public function setBrandLabel($filter, $activeLabel)
    {
        if (strtolower((string) $filter->getName()) == 'brand') {
            $this->brandLabel = $activeLabel;
        }
    }

    /**
     * @return array|bool
     */
    public function getBrandLabel()
    {
        return $this->brandLabel;
    }
    /**
     * @return mixed
     */
    public function getCurrentCategory(): ?CategoryInterface
    {
        $currentCategory = $this->coreRegistry->registry('current_category');

        if ($currentCategory && $this->checkCategory($currentCategory)) {
            return $currentCategory;
        }
        return null;
    }

    /**
     * @return CategoryInterface|null
     */
    public function getParentCurrentCategory(): ?CategoryInterface
    {
        $parentCategories = null;

        $currentCategory = $this->getCurrentCategory();

        if ($currentCategory && $currentCategory->getId()) {
            $parentCategories = $currentCategory->getParentCategory();
        }
        return $parentCategories;
    }

    /**
     * @return bool|string
     */
    public function getViewCategoryData():string
    {
        $fBData = [];
        $fBData['content_category'] = $this->getParentCurrentCategory();
        $fBData['content_name'] = $this->getCurrentCategory();

        $result = array_reduce(array_keys($fBData), function ($acc, $dataKey) use ($fBData) {
            $element = $fBData[$dataKey];
            if ($element && $element->getId()) {
                $acc[$dataKey] = $element->getName();
            }
            return $acc;
        }, []);

        $brandContent = $this->getBrandLabel();
        if ($brandContent && !empty($brandContent)) {
            $result['content_brand'] = $this->getBrandLabel();
        }

        if (!empty($result)) {
            $result['content_type'] = 'product';
        }
        return $this->jsonSerializer->serialize($result);
    }

    /**
     * @return string
     */
    public function getPurchaseData(): string
    {
        /**
         * @var $order \Magento\Sales\Model\Order
         */
        $order = $this->checkoutSession->getLastRealOrder();
        $orderCurrency = $order->getOrderCurrency();
        $preparedData = $this->prepareOrderItems($order->getAllVisibleItems());
        $purchaseData = [
            'currency'     => $orderCurrency->getCurrencyCode(),
            'contents'     => $preparedData,
            'value'        => $order->getGrandTotal(),
            'content_type' => 'product'
        ];

        return $this->jsonSerializer->serialize($purchaseData);
    }
    /**
     * @param CategoryInterface $category
     * @return bool
     */
    public function checkCategory(CategoryInterface $category): bool
    {
        return !in_array($category->getId(), self::EXCLUDED_ID_CATEGORIES);
    }

    /**
     * @param array $orderItems
     * @return array
     */
    public function prepareOrderItems(array $orderItems): array
    {
        $result = array_reduce($orderItems, function ($acc, $item) {
            /**
             * @var $item \Magento\Sales\Model\Order\Item
             */
            $acc[] = ['content_name' => $item->getName(), 'content_id' => $item->getSku()];
            return $acc;
        }, []);

        return $result;
    }
}
