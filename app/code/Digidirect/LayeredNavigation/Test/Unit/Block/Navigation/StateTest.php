<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Block\Navigation;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * FilterRenderer block mock
     * @var \Digidirect\LayeredNavigation\Block\Navigation\State
     */
    protected $_state;

    /**
     * FilterItem model mock
     * @var \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected $_filterItem;

    /**
     * FilterItem model mock
     * @var \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected $_filterItem2;

    /**
     * Set up required common objects
     * @return void
     */
    public function setUp()
    {
        $reflector = new \ReflectionClass('\Digidirect\LayeredNavigation\Block\Navigation\State');
        $this->_state = $reflector->newInstanceWithoutConstructor();

        $this->_filterItem = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item')
            ->setMethods(['getValue', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_filterItem2 = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item')
            ->setMethods(['getValue', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test case for modifyAppliedFiltersArray
     * @return void
     */
    public function testModifyAppliedFiltersArray()
    {
        $this->_filterItem->expects($this->any())
            ->method('getName')
            ->willReturn('color');

        $this->_filterItem->expects($this->any())
            ->method('getValue')
            ->willReturn(['red', 'green', 'blue']);

        $this->_filterItem2->expects($this->any())
            ->method('getName')
            ->willReturn('price');

        $this->_filterItem2->expects($this->any())
            ->method('getValue')
            ->willReturn('20-30');

        $activeFilters = [
            $this->_filterItem,
            $this->_filterItem2,
        ];

        $expectedResult = [
            'color' => [
                'red-green-blue' => $this->_filterItem
            ],
            'price' => [
                '20-30' => $this->_filterItem2
            ],
        ];

        $this->assertEquals($expectedResult, $this->_state->modifyAppliedFiltersArray($activeFilters));
    }
}
