<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Block\Navigation;

use Digidirect\LayeredNavigation\Model\Source\DisplayMode;

class FilterRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * FilterRenderer block mock
     * @var \Digidirect\LayeredNavigation\Block\Navigation\FilterRenderer
     */
    protected $_filterRenderer;

    /**
     * FilterSetting model mock
     * @var \Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface
     */
    protected $_filterSetting;

    /**
     * FilterItem model mock
     * @var \Magento\Catalog\Model\Layer\Filter\Item
     */
    protected $_filterItem;

    /**
     * Request model mock
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Set up required common objects
     * @return void
     */
    public function setUp()
    {
        $reflector = new \ReflectionClass('\Digidirect\LayeredNavigation\Block\Navigation\FilterRenderer');
        $this->_filterRenderer = $reflector->newInstanceWithoutConstructor();

        $this->_filterSetting = $this->getMockBuilder('\Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_filterItem = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item')
            ->setMethods(['getFilter', 'getRequestVar', 'getValue', 'getRemoveUrl'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_request = $this->getMockBuilder('\Magento\Framework\App\Request')
            ->setMethods(['getParam'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestReflector = new \ReflectionProperty(
            '\Digidirect\LayeredNavigation\Block\Navigation\FilterRenderer',
            '_request'
        );

        $requestReflector->setAccessible(true);
        $requestReflector->setValue($this->_filterRenderer, $this->_request);
    }

    /**
     * Test case for getting template by filter setting
     * @return void
     */
    public function testGetTemplateByFilterSetting()
    {
        $this->_filterSetting->expects($this->at(0))
            ->method('getDisplayMode')
            ->willReturn(DisplayMode::MODE_SLIDER);

        $this->_filterSetting->expects($this->at(1))
            ->method('getDisplayMode')
            ->willReturn(DisplayMode::MODE_DROPDOWN);

        $this->_filterSetting->expects($this->at(2))
            ->method('getDisplayMode')
            ->willReturn('unknown');

        $this->assertEquals(
            'layer/filter/slider.phtml',
            $this->_filterRenderer->getTemplateByFilterSetting($this->_filterSetting)
        );

        $this->assertEquals(
            'layer/filter/dropdown.phtml',
            $this->_filterRenderer->getTemplateByFilterSetting($this->_filterSetting)
        );

        $this->assertEquals(
            'layer/filter/default.phtml',
            $this->_filterRenderer->getTemplateByFilterSetting($this->_filterSetting)
        );
    }

    /**
     * Test case for checking filter item
     * @return void
     */
    public function testIsFilterItemChecked()
    {
        $this->_filterItem->expects($this->any())
            ->method('getFilter')
            ->willReturnSelf();

        $this->_filterItem->expects($this->any())
            ->method('getRequestVar')
            ->willReturn('color');

        $this->_filterItem->expects($this->any())
            ->method('getValue')
            ->willReturn('green');

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('color')
            ->willReturn('red,green,blue');

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('color')
            ->willReturn('yellow,pink,blue');

        $this->assertEquals(1, $this->_filterRenderer->checkedFilter($this->_filterItem));
        $this->assertEquals(0, $this->_filterRenderer->checkedFilter($this->_filterItem));
    }

    /**
     * Test case for getting clear url
     * @return void
     */
    public function testGetClearUrl()
    {
        $this->assertEquals('', $this->_filterRenderer->getClearUrl());

        $viewVarsReflector = new \ReflectionProperty(
            '\Digidirect\LayeredNavigation\Block\Navigation\FilterRenderer',
            '_viewVars'
        );

        $this->_filterItem->expects($this->any())
            ->method('getFilter')
            ->willReturnSelf();

        $this->_filterItem->expects($this->any())
            ->method('getRequestVar')
            ->willReturn('color');

        $this->_filterItem->expects($this->any())
            ->method('getValue')
            ->willReturn('green');

        $this->_filterItem->expects($this->any())
            ->method('getRemoveUrl')
            ->willReturn('?color=red,blue');

        $this->_request->expects($this->at(0))
            ->method('getParam')
            ->with('color')
            ->willReturn('red,green,blue');

        $this->_request->expects($this->at(1))
            ->method('getParam')
            ->with('color')
            ->willReturn('red,blue');

        $viewVarsReflector->setAccessible(true);
        $viewVarsReflector->setValue($this->_filterRenderer, [
            'filterItems' => [
                $this->_filterItem
            ]
        ]);

        $expectedClearUrl = '?color=red,blue';
        $this->assertEquals($expectedClearUrl, $this->_filterRenderer->getClearUrl());
        $this->assertEquals('', $this->_filterRenderer->getClearUrl());
    }
}
