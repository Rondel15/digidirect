<?php
namespace Digidirect\Collect\Model\Plugin\Checkout\Controller\Cart;

use Digidirect\Collect\Helper\Data;

/**
 * Set a flag to the response with information that add-to-cart validation was failed.
 *
 * Class Add
 * @package Digidirect\Collect\Model\Plugin\Checkout\Controller\Cart
 */
class Add
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Add constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->registry = $registry;
        $this->json = $json;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\Add $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function afterExecute(\Magento\Checkout\Controller\Cart\Add $subject, $result)
    {
        if ($this->registry->registry(Data::ADD_TO_CART_VALIDATION_FAILED_FLAG)) {
            if ($subject->getResponse()->getContent()) {
                $content = $this->json->unserialize($subject->getResponse()->getContent());
            } else {
                $content = [];
            }
            $content[Data::ADD_TO_CART_VALIDATION_FAILED_FLAG] = true;
            $subject->getResponse()->setContent($this->json->serialize($content));

            $this->registry->unregister(Data::ADD_TO_CART_VALIDATION_FAILED_FLAG);
        }
        return $result;
    }
}
