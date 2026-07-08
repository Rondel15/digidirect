<?php

namespace Digidirect\FreeGift\Test\Unit\Model;

class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Digidirect\FreeGift\Model\Cart
     */
    protected $_cartModel;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var \Digidirect\FreeGift\Helper\Messages
     */
    protected $_giftMessagesHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface|\Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->_quote = $this->getMockBuilder('Magento\Quote\Api\Data\CartInterface')
            ->setMethods(['setVirtualItemsQty', 'getAllVisibleItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->_giftRegistry = $this->getMockBuilder('Digidirect\FreeGift\Model\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_stockRegistry = $this->getMockBuilder('Magento\CatalogInventory\Model\StockRegistry')
            ->setMethods(['getStockItem', 'getManageStock', 'getQty'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_giftMessagesHelper = $this->getMockBuilder('Digidirect\FreeGift\Helper\Messages')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productFactory = $this->getMockBuilder('Magento\Catalog\Model\ProductFactory')
            ->setMethods(['create', 'getCollection', 'addAttributeToSelect', 'addFieldToFilter'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_productCollection = $this->getMockBuilder('Magento\Catalog\Model\ResourceModel\Product\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->setMethods([
                'getId',
                'getProductId',
                'getStore',
                'getWebsiteId',
                'getTypeId',
                'getQty',
                'getProduct',
                'getIsVirtual',
                'isSalable',
                'getProductOptionsCollection'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_cartModel = $this->getMockBuilder('Digidirect\FreeGift\Model\Cart')
            ->setMethods([
                'addProduct',
                'getQuote',
                'save',
                'getItems',
                'hasData',
                'getId'
            ])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->setProtectedProperty($this->_cartModel, '_giftRegistry', $this->_giftRegistry);
        $this->setProtectedProperty($this->_cartModel, 'stockRegistry', $this->_stockRegistry);
        $this->setProtectedProperty($this->_cartModel, '_giftMessagesHelper', $this->_giftMessagesHelper);
        $this->setProtectedProperty($this->_cartModel, '_productFactory', $this->_productFactory);
    }

    /**
     * Test case add product when product is virtual
     *
     * @return void
     */
    public function testAddVirtualProduct()
    {
        $this->_product->expects($this->any())
            ->method('getTypeId')
            ->willReturn('virtual');

        $this->_cartModel->expects($this->any())
            ->method('getQuote')
            ->willReturnSelf();

        $this->assertTrue($this->_cartModel->addFreeGiftToCart($this->_product, 1000, false, ['product_id' => 100]));
    }

    /**
     * Test case add product when product when manage stock is disabled
     *
     * @return void
     */
    public function testAddProductManageStockDisabled()
    {
        $this->_product->expects($this->any())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->_product->expects($this->any())
            ->method('getId')
            ->willReturn(100);

        $this->_product->expects($this->any())
            ->method('getStore')
            ->willReturnSelf();

        $this->_product->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->_stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->with(100, 1)
            ->willReturnSelf();

        $this->_stockRegistry->expects($this->any())
            ->method('getManageStock')
            ->willReturn(false);

        $this->_cartModel->expects($this->any())
            ->method('getQuote')
            ->willReturnSelf();

        $this->_cartModel->expects($this->once())
            ->method('addProduct')
            ->willReturnSelf();

        $this->assertTrue($this->_cartModel->addFreeGiftToCart($this->_product, 1000));
    }

    /**
     * Test case add product when product is not available
     *
     * @return void
     */
    public function testAddProductNoAvailableQty()
    {
        $this->_product->expects($this->any())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->_product->expects($this->any())
            ->method('getId')
            ->willReturn(100);

        $this->_product->expects($this->any())
            ->method('getStore')
            ->willReturnSelf();

        $this->_product->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->_stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->with(100, 1)
            ->willReturnSelf();

        $this->_stockRegistry->expects($this->any())
            ->method('getManageStock')
            ->willReturn(true);

        $this->_stockRegistry->expects($this->any())
            ->method('getQty')
            ->willReturn(20);

        $this->_cartModel->expects($this->any())
            ->method('getItems')
            ->willReturn([$this->_product]);

        $this->_product->expects($this->any())
            ->method('getProductId')
            ->willReturn(100);

        $this->_product->expects($this->any())
            ->method('getQty')
            ->willReturn(15);

        $this->assertFalse($this->_cartModel->addFreeGiftToCart($this->_product, 1000));
    }

    /**
     * Test case add product with negative qty
     *
     * @return void
     */
    public function testNegativeQty()
    {
        $this->assertFalse($this->_cartModel->addFreeGiftToCart($this->_product, -10));
    }

    /**
     * Test case get new items without quote
     *
     * @return void
     */
    public function testGetNewItemsNoQuote()
    {
        $this->_cartModel->expects($this->any())
            ->method('getQuote')
            ->willReturnSelf();

        $this->_cartModel->expects($this->any())
            ->method('getId')
            ->willReturn(0);

        $this->assertEquals([], $this->_cartModel->getNewFreeGiftItems());
    }

    /**
     * Test case get new items without free gift rules
     *
     * @return void
     */
    public function testGetNewItemsNoFreeGifts()
    {
        $this->_cartModel->expects($this->any())
            ->method('getQuote')
            ->willReturnSelf();

        $this->_cartModel->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->_giftRegistry->expects($this->any())
            ->method('getLimits')
            ->willReturn([
                '_groups' => []
            ]);

        $this->assertEquals([], $this->_cartModel->getNewFreeGiftItems());
    }

    /**
     * Test case get new items without free gift rules
     *
     * @return void
     */
    public function testGetNewItems()
    {
        $this->_cartModel->expects($this->any())
            ->method('getQuote')
            ->willReturnSelf();

        $this->_cartModel->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $this->_giftRegistry->expects($this->any())
            ->method('getLimits')
            ->willReturn([
                '_groups' => [
                    1 => ['sku' => ['free-product']]
                ]
            ]);

        $this->_productFactory->expects($this->any())
            ->method('create')
            ->willReturnSelf();

        $this->_productFactory->expects($this->any())
            ->method('getCollection')
            ->willReturnSelf();

        $this->_productFactory->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();

        $this->_productFactory->expects($this->any())
            ->method('addFieldToFilter')
            ->willReturn($this->_productCollection);

        $this->_productCollection->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$this->_product]));

        $this->_productCollection->expects($this->any())
            ->method('getSize')
            ->willReturn(1);

        $this->_product->expects($this->any())
            ->method('getTypeId')
            ->willReturn('simple');

        $this->_product->expects($this->any())
            ->method('getId')
            ->willReturn(100);

        $this->_product->expects($this->any())
            ->method('getStore')
            ->willReturnSelf();

        $this->_product->expects($this->any())
            ->method('isSalable')
            ->willReturn(true);

        $this->_product->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(1);

        $this->_stockRegistry->expects($this->any())
            ->method('getStockItem')
            ->with(100, 1)
            ->willReturnSelf();

        $this->_stockRegistry->expects($this->any())
            ->method('getManageStock')
            ->willReturn(false);

        $this->_product->expects($this->any())
            ->method('getProductOptionsCollection')
            ->willReturn([]);

        $this->assertEquals($this->_productCollection, $this->_cartModel->getNewFreeGiftItems());
    }
}
