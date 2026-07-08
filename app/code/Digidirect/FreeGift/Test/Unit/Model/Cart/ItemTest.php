<?php

namespace Digidirect\FreeGift\Test\Unit\Model\Cart;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_itemModel;

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_quoteItem;

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->_quoteItem = $this->getMockBuilder('Magento\Quote\Model\Quote\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_itemModel = $this->_objectManager->getObject('Digidirect\FreeGift\Model\Cart\Item', []);
    }

    /**
     * @return void
     */
    public function testGetRuleIdNoRule()
    {
        $this->_quoteItem->expects($this->any())
            ->method('getData')
            ->with(\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY)
            ->willReturn(0);

        $this->assertNull($this->_itemModel->getRuleId($this->_quoteItem));
    }

    /**
     * @return void
     */
    public function testGetRuleId()
    {
        $this->_quoteItem->expects($this->any())
            ->method('getBuyRequest')
            ->willReturn([
                'options' => [
                    \Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY => 30
                ]
            ]);

        $this->assertEquals(30, $this->_itemModel->getRuleId($this->_quoteItem));
    }
}
