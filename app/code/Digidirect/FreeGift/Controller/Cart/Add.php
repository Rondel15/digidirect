<?php

namespace Digidirect\FreeGift\Controller\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Digidirect\FreeGift\Model\Cart\Item;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Digidirect\FreeGift\Model\Cart
     */
    protected $_giftCart;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Digidirect\FreeGift\Model\Registry $giftRegistry
     * @param \Digidirect\FreeGift\Model\Cart $giftCart
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Digidirect\FreeGift\Model\Registry $giftRegistry,
        \Digidirect\FreeGift\Model\Cart $giftCart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->_giftRegistry = $giftRegistry;
        $this->_giftCart = $giftCart;
        $this->_productRepository = $productRepository;
    }

    /**
     * Initialize product instance from request data
     *
     * @param int $productId
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct($productId = null)
    {
        if ($productId === null) {
            $productId = (int)$this->getRequest()->getParam('product_id');
        }

        if ($productId) {
            try {
                return $this->_productRepository->getById($productId);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('Requested Free Gift product does not exist.'));
                return false;
            }
        }
        $this->messageManager->addErrorMessage(__('Invalid Request.'));
        return false;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $rules = $this->getRequest()->getParam('rule_id', []);
        $superAttributes = $this->getRequest()->getParam('super_attribute', []);
        $options = $this->getRequest()->getParam('options', []);

        $addedProducts = [];
        $limits = $this->_giftRegistry->getLimits();
        foreach ($rules as $ruleId => $products) {
            foreach ($products as $productId) {
                $params = [
                    'product_id' => $productId,
                    'super_attribute' => $superAttributes[$ruleId][$productId] ?? [],
                    'options' => $options[$ruleId][$productId] ?? [],
                ];
                $this->getRequest()->setParams($params);
                $product = $this->_initProduct($productId);
                if (!$product) {
                    continue;
                }

                $sku = $product->getSku();
                if (!$ruleId) {
                    $ruleId = isset($limits[$sku]) && $limits[$sku] > 0 ? $limits[$sku]['rule_id'] : false;
                    if (!$ruleId) {
                        foreach ($limits['_groups'] as $salesRuleId => $rule) {
                            if (in_array($sku, $rule['sku'])) {
                                $ruleId = $salesRuleId;
                            }
                        }
                    }
                }

                if ($ruleId) {
                    try {
                        $product->setData(Item::FREE_GIFT_ADDED_BY_RULE_ID, $ruleId);
                        $this->_giftRegistry->restore($sku);
                        $this->_giftCart->addFreeGiftToCart($product, 1, $ruleId, $params, false);
                        $this->_giftCart->saveQuote();
                        $addedProducts[] = $product->getName();
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage(__('Something went wrong.'));
                    }
                }
            }
        }

        if (!empty($addedProducts)) {
            $this->messageManager->addSuccessMessage(__(
                'You added %1 to your shopping cart.',
                implode(', ', $addedProducts)
            ));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();
        return $resultRedirect;
    }
}
