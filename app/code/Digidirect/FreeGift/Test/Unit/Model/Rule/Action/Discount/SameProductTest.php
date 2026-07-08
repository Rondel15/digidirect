<?php

namespace Digidirect\FreeGift\Test\Unit\Model\Rule\Action\Discount;

class SameProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\FreeGift\Model\Rule\Action\Discount\SameProduct
     */
    protected $_sameProductActionModel;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_giftItem;

    /**
     * @var \Digidirect\FreeGift\Model\Cart
     */
    protected $_giftCart;

    /**
     * @var \Magento\SalesRule\Model\Rule
     */
    protected $_salesRule;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_quoteItem;

    /**
     * @var \Digidirect\FreeGift\Api\Data\RuleInterface
     */
    protected $_freeGiftRule;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\Discount\DataFactory
     */
    protected $_discountDataFactory;

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->_giftItem = $this->getMockBuilder('Digidirect\FreeGift\Model\Cart\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_giftCart = $this->getMockBuilder('Digidirect\FreeGift\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_giftRegistry = $this->getMockBuilder('Digidirect\FreeGift\Model\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_salesRule = $this->getMockBuilder('Magento\SalesRule\Model\Rule')
            ->setMethods([
                'getDiscountAmount',
                'getDiscountStep',
                'getActions',
                'validate',
                'getDiscountQty'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_quote = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_quoteItem = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_freeGiftRule = $this->getMockBuilder('Digidirect\FreeGift\Api\Data\RuleInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_discountDataFactory = $this->getMockBuilder('Magento\SalesRule\Model\Rule\Action\Discount\DataFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_sameProductActionModel = $this->_objectManager->getObject(
            'Digidirect\FreeGift\Model\Rule\Action\Discount\SameProduct',
            [
                '_giftRegistry' => $this->_giftRegistry,
                '_giftItem' => $this->_giftItem,
                '_giftCart' => $this->_giftCart,
                'discountDataFactory' => $this->_discountDataFactory
            ]
        );
    }

    /**
     * Test case for free gift calculation
     *
     * @return void
     */
    public function testCalculate()
    {
        $this->_giftCart->expects($this->once())
            ->method('getAllowedProductTypes')
            ->willReturn([null]);

        $this->_salesRule->expects($this->once())
            ->method('getDiscountStep')
            ->willReturn(5);

        $this->_salesRule->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn(10);

        $this->_salesRule->expects($this->once())
            ->method('getDiscountQty')
            ->willReturn(0);

        $this->_quoteItem->expects($this->once())
            ->method('getQty')
            ->willReturn(20);

        $this->_quoteItem->expects($this->once())
            ->method('getProduct')
            ->willReturnSelf();

        $this->_quoteItem->expects($this->once())
            ->method('getData')
            ->with('sku')
            ->willReturn('product-sku');

        $this->_giftRegistry->expects($this->once())
            ->method('addFreeGiftItem')
            ->with(['product-sku'], 40, null);

        $this->_sameProductActionModel->calculate($this->_salesRule, $this->_quoteItem, 1);
    }
}
