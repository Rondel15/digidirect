<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Helper;

class UrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Url builder reflection object - to make possible access to protected properties
     * @var \ReflectionClass
     */
    protected $_urlBuilder;

    /**
     * Url builder Helper Object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layer;

    /**
     * Layer Object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * Filter mock object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $state;

    /**
     * Layer state object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * Filter item object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterItem;

    /**
     * Second filer mock object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $secondFilter;

    /**
     * Magento registry
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * Event manager
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * Magento Url Builder
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $magentoUrlBuilder;

    /**
     * Filter Settings
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterSettingHelper;

    /**
     * Set up all need objects
     * @return void
     */
    public function setUp()
    {
        $this->filter = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Attribute')
            ->setMethods(['getRequestVar', 'getName', 'getLayer', 'getState', 'getFilters'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterItem = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->secondFilter = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\Attribute')
            ->setMethods(['getName'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_urlBuilder = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\UrlBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['_getLoader'])
            ->getMock();

        $this->registry = $this->getMockBuilder('\Magento\Framework\Registry')
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

        $registryProperty = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\UrlBuilder', 'registry');
        $registryProperty->setAccessible(true);
        $registryProperty->setValue($this->_urlBuilder, $this->registry);

        $excludeParamsProperty = new \ReflectionProperty(
            '\Digidirect\LayeredNavigation\Helper\UrlBuilder',
            '_excludedParams'
        );
        $excludeParamsProperty->setAccessible(true);
        $excludeParamsProperty->setValue($this->_urlBuilder, ['id']);

        $this->layer = $this->getMockBuilder('\Magento\Catalog\Model\Layer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->state = $this->getMockBuilder('\Magento\Catalog\Model\Layer\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->setMethods(['getParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestProperty = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\UrlBuilder', '_request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_urlBuilder, $this->request);

        $this->eventManager = $this->getMockBuilder('\Magento\Framework\Event\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $eventProperty = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\UrlBuilder', '_eventManager');
        $eventProperty->setAccessible(true);
        $eventProperty->setValue($this->_urlBuilder, $this->eventManager);

        $this->magentoUrlBuilder = $this->getMockBuilder('\Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $urlBuilderProperty = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\UrlBuilder', '_urlBuilder');
        $urlBuilderProperty->setAccessible(true);
        $urlBuilderProperty->setValue($this->_urlBuilder, $this->magentoUrlBuilder);

        $this->filterSettingHelper = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\FilterSetting')
            ->setMethods(['getSettingByLayerFilter', 'isMultiselect'])
            ->disableOriginalConstructor()
            ->getMock();

        $filterSettingHelperProperty = new \ReflectionProperty(
            '\Digidirect\LayeredNavigation\Helper\UrlBuilder',
            'filterSettingHelper'
        );
        $filterSettingHelperProperty->setAccessible(true);
        $filterSettingHelperProperty->setValue($this->_urlBuilder, $this->filterSettingHelper);
    }

    /**
     * Test case for multiple filter selected
     * @return void
     */
    public function testModifyQueryStringWithTwoFiltersApplied()
    {
        $originalFilter = 'color';
        $this->filter->expects($this->any())
            ->method('getLayer')
            ->willReturn($this->layer);

        $this->filter->expects($this->any())
            ->method('getName')
            ->willReturn($originalFilter);

        $this->layer->expects($this->any())
            ->method('getState')
            ->will($this->returnValue($this->state));

        $this->state->expects($this->any())
            ->method('getFilters')
            ->willReturn([$this->filterItem]);

        $this->filterItem->expects($this->any())
            ->method('getName')
            ->willReturn('test_filter');

        $this->filterItem->expects($this->any())
            ->method('getFilter')
            ->willReturn($this->secondFilter);

        $this->secondFilter->expects($this->any())
            ->method('getRequestVar')
            ->willReturn('size');

        $urlBuilder = new \ReflectionClass('\Digidirect\LayeredNavigation\Helper\UrlBuilder');
        $method = $urlBuilder->getMethod('_modifyQueryString');
        $method->setAccessible(true);

        $this->request->expects($this->any())
            ->method('getParam')
            ->with('size')
            ->willReturn(7);

        $result = $method->invokeArgs($this->_urlBuilder, [$this->filter, [$originalFilter => '15']]);
        $this->assertEquals([$originalFilter => 15], $result);
    }

    /**
     * Test case when one filter applied only
     * @return void
     */
    public function testModifyQueryStringWithOneFilterApplied()
    {
        $originalFilter = 'color';
        $this->filter->expects($this->any())
            ->method('getLayer')
            ->willReturn($this->layer);

        $this->filter->expects($this->any())
            ->method('getName')
            ->willReturn($originalFilter);

        $this->layer->expects($this->any())
            ->method('getState')
            ->will($this->returnValue($this->state));

        $this->state->expects($this->any())
            ->method('getFilters')
            ->willReturn([$this->filterItem]);

        $this->filterItem->expects($this->any())
            ->method('getFilter')
            ->willReturn($this->filter);

        $this->filterItem->expects($this->any())
            ->method('getName')
            ->willReturn($originalFilter);

        $urlBuilder = new \ReflectionClass('\Digidirect\LayeredNavigation\Helper\UrlBuilder');
        $method = $urlBuilder->getMethod('_modifyQueryString');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->_urlBuilder, [$this->filter, [$originalFilter => 15]]);

        $this->assertEquals([$originalFilter => 15], $result);
    }

    /**
     * Test case for Url builder
     * @return array
     */
    protected function buildUrlMock($isMultiselect = false)
    {
        $this->registry->expects($this->any())
            ->method('registry')
            ->willReturn([
                'color' => 'red',
                'size' => 34,
                'id' => 100,
            ]);

        $this->filter->expects($this->any())
            ->method('getRequestVar')
            ->willReturn('color');

        $originalFilter = 'size';
        $this->filter->expects($this->any())
            ->method('getName')
            ->willReturn($originalFilter);

        $this->filter->expects($this->any())
            ->method('getLayer')
            ->willReturnSelf();

        $this->filter->expects($this->any())
            ->method('getState')
            ->willReturnSelf();

        $this->filter->expects($this->any())
            ->method('getFilters')
            ->willReturn([
                $this->filterItem
            ]);

        $this->filterItem->expects($this->any())
            ->method('getName')
            ->willReturn($originalFilter);

        $this->request->expects($this->any())
            ->method('getParams')
            ->willReturn([]);

        $this->filterSettingHelper->expects($this->any())
            ->method('getSettingByLayerFilter')
            ->willReturnSelf();

        $this->magentoUrlBuilder->expects($this->any())
            ->method('getUrl')
            ->willReturnArgument(1);

        $this->filterSettingHelper->expects($this->any())
            ->method('isMultiselect')
            ->willReturn($isMultiselect);

        $result = $this->_urlBuilder->buildUrl($this->filter, 'blue');
        return $result['_query'];
    }

    /**
     * Test case for Url builder when multiselect is enabled
     * @return void
     */
    public function testBuildUrlMultiSelect()
    {
        $result = $this->buildUrlMock(true);
        $this->assertEquals(['color' => 'red,blue', 'size' => 34], $result);
    }

    /**
     * Test case for Url builder when multiselect is disabled
     * @return void
     */
    public function testBuildUrlNoMultiSelect()
    {
        $result = $this->buildUrlMock();
        $this->assertEquals(['color' => 'blue', 'size' => 34], $result);
    }
}
