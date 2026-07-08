<?php

namespace Digidirect\FreeGift\Test\Unit\Model;

class RegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_registryModel;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_quoteItem;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_giftItem;

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->_checkoutSession = $this->getMockBuilder('Magento\Checkout\Model\Session')
            ->setMethods(['getQuote', 'getFreegiftItems', 'getAllItems'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_quoteItem = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->setMethods(['getProduct', 'getData', 'getQty', 'getOptions'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_giftItem = $this->getMockBuilder('Digidirect\FreeGift\Model\Cart\Item')
            ->setMethods(['isFreeGiftItem', 'getRuleId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_registryModel = $this->_objectManager->getObject('Digidirect\FreeGift\Model\Registry', [
            'resourceSession' => $this->_checkoutSession,
            '_giftItem' => $this->_giftItem,
        ]);
    }

    /**
     * Test case get limits
     *
     * @return void
     */
    public function testGetLimits()
    {
        $this->_checkoutSession->expects($this->any())
            ->method('getQuote')
            ->willReturnSelf();

        $this->_checkoutSession->expects($this->any())
            ->method('getFreegiftItems')
            ->willReturn([
                '_groups' => [
                    1 => ['sku' => 'gift1', 'qty' => 2],
                    2 => ['sku' => 'gift2', 'qty' => 10],
                    3 => ['sku' => 'gift3', 'qty' => 4],
                ]
            ]);

        $this->_quoteItem->expects($this->any())
            ->method('getProduct')
            ->willReturnSelf();

        $this->_quoteItem->expects($this->at(1))
            ->method('getData')
            ->with('sku')
            ->willReturn('gift2');

        $this->_quoteItem->expects($this->any())
            ->method('getOptions')
            ->willReturn([]);

        $this->_quoteItem->expects($this->any())
            ->method('getQty')
            ->willReturn(10);

        $this->_checkoutSession->expects($this->once())
            ->method('getAllItems')
            ->willReturn([
                $this->_quoteItem,
                $this->_quoteItem,
            ]);

        $this->_giftItem->expects($this->any(1))
            ->method('isFreeGiftItem')
            ->willReturn(true);

        $this->_giftItem->expects($this->at(2))
            ->method('isFreeGiftItem')
            ->willReturn(false);

        $this->_giftItem->expects($this->at(1))
            ->method('getRuleId')
            ->willReturn(2);

        $expectedResult = [
            '_groups' => [
                1 => ['sku' => 'gift1', 'qty' => 2],
                3 => ['sku' => 'gift3', 'qty' => 4],
            ]
        ];

        $this->assertEquals($expectedResult, $this->_registryModel->getLimits());
    }
}
