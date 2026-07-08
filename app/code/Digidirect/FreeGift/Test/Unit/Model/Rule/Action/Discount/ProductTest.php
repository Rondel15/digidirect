<?php

namespace Digidirect\FreeGift\Test\Unit\Model\Rule\Action\Discount;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\FreeGift\Model\Rule\Action\Discount\Product
     */
    protected $_productActionModel;

    /**
     * @var \Digidirect\FreeGift\Api\RuleRepositoryInterface
     */
    protected $_ruleRepository;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

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
        $this->_ruleRepository = $this->getMockBuilder('Digidirect\FreeGift\Api\RuleRepositoryInterface')
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
        $this->_productActionModel = $this->_objectManager->getObject(
            'Digidirect\FreeGift\Model\Rule\Action\Discount\Product',
            [
                '_ruleRepository' => $this->_ruleRepository,
                '_giftRegistry' => $this->_giftRegistry,
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
        $this->_giftRegistry->expects($this->once())
            ->method('getApplyAttempt')
            ->willReturn(true);

        $this->_ruleRepository->expects($this->once())
            ->method('loadBySalesrule')
            ->willReturn($this->_freeGiftRule);

        $this->_freeGiftRule->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->_freeGiftRule->expects($this->once())
            ->method('getSkuArray')
            ->willReturn(['sku1']);

        $this->_quoteItem->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->_quote);

        $this->_quoteItem->expects($this->once())
            ->method('getProduct')
            ->willReturnSelf();

        $this->_quoteItem->expects($this->once())
            ->method('getQty')
            ->willReturn(20);

        $this->_quote->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([$this->_quoteItem]);

        $this->_salesRule->expects($this->once())
            ->method('getActions')
            ->willReturnSelf();

        $this->_salesRule->expects($this->once())
            ->method('validate')
            ->willReturn(true);

        $this->_salesRule->expects($this->once())
            ->method('getDiscountStep')
            ->willReturn(5);

        $this->_salesRule->expects($this->once())
            ->method('getDiscountAmount')
            ->willReturn(10);

        $this->_salesRule->expects($this->once())
            ->method('getDiscountQty')
            ->willReturn(0);

        $this->_freeGiftRule->expects($this->once())
            ->method('getType')
            ->willReturn(0);

        $this->_giftRegistry->expects($this->once())
            ->method('addFreeGiftItem')
            ->with(['sku1'], 40, null);

        $this->_productActionModel->calculate($this->_salesRule, $this->_quoteItem, 1);
    }
}
