<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RestrictAddToCart;

/**
 * Class Add
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\Controller\Cart
 */
class Add
{
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * Add constructor.
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param CustomerCart $cart
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     */
    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $json,
        CustomerCart $cart,
        ExtendedCartPriceRule $extendedCartPriceRule
    ) {
        $this->json = $json;
        $this->cart = $cart;
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @param null|string $result
     * @return mixed
     */
    public function afterExecute(\Magento\Checkout\Controller\Cart\Add $subject, $result)
    {
        $restrict = $this->extendedCartPriceRule->getRestrictAddToCartErrorMessages($this->cart->getQuote());
        if (!empty($restrict) && $subject->getRequest()->isAjax()) {
            $content = $this->json->unserialize($subject->getResponse()->getContent());
            $content[RestrictAddToCart::ERROR_CODE] = true;
            $subject->getResponse()->setContent($this->json->serialize($content));
        }
        return $result;
    }
}
