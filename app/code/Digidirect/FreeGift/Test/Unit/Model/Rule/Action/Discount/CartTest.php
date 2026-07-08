<?php

namespace Digidirect\FreeGift\Test\Unit\Model\Rule\Action\Discount;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\FreeGift\Model\Rule\Action\Discount\Cart
     */
    protected $_cartActionModel;

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
        $this->_cartActionModel = $this->_objectManager->getObject('Digidirect\FreeGift\Model\Rule\Action\Discount\Cart', [
            '_ruleRepository' => $this->_ruleRepository,
            '_giftRegistry' => $this->_giftRegistry,
            'discountDataFactory' => $this->_discountDataFactory
        ]);
    }

    /**
     * Test case for discount calculation. Add one free gift sku
     *
     * @return void
     */
    public function testCalculateTypeOne()
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
            ->willReturn(['sku1', 'sku2', 'sku2']);

        $this->_quoteItem->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->_quote);

        $this->_freeGiftRule->expects($this->once())
            ->method('getType')
            ->willReturn(1);

        $this->_giftRegistry->expects($this->once())
            ->method('addFreeGiftItem');

        $this->_cartActionModel->calculate($this->_salesRule, $this->_quoteItem, 1);
    }

    /**
     * Test case for discount calculation. Add all free gift sku
     *
     * @return void
     */
    public function testCalculateTypeAll()
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
            ->willReturn(['sku1', 'sku2', 'sku2']);

        $this->_quoteItem->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->_quote);

        $this->_giftRegistry->expects($this->once())
            ->method('addFreeGiftItem');

        $this->_cartActionModel->calculate($this->_salesRule, $this->_quoteItem, 1);
    }
}
