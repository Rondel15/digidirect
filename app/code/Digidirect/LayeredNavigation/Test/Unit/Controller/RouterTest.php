<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Controller;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * Router mock
     * @var \Digidirect\LayeredNavigation\Controller\Router
     */
    protected $_router;

    /**
     * Request model mock
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\Url
     */
    protected $_urlHelper;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\UrlParser
     */
    protected $_urlParser;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\Data
     */
    protected $_seoHelper;

    /**
     * Set up required common objects
     * @return void
     */
    public function setUp()
    {
        $this->_scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfig')
            ->setMethods(['getValue', 'isSetFlag'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_urlHelper = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\Url')
            ->disableOriginalConstructor()
            ->getMock();

        $scopeConfigReflector = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\Url', 'scopeConfig');
        $scopeConfigReflector->setAccessible(true);
        $scopeConfigReflector->setValue($this->_urlHelper, $this->_scopeConfig);

        $reflector = new \ReflectionClass('\Digidirect\LayeredNavigation\Helper\UrlParser');
        $this->_urlParser = $reflector->newInstanceWithoutConstructor();

        $this->_seoHelper = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\Data')
            ->setMethods(['getOptionsSeoData', 'parseRequestUri'])
            ->disableOriginalConstructor()
            ->getMock();

        $seoHelperReflector = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\UrlParser', 'seoHelper');
        $seoHelperReflector->setAccessible(true);
        $seoHelperReflector->setValue($this->_urlParser, $this->_seoHelper);

        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_router = $this->_objectManager->getObject('\Digidirect\LayeredNavigation\Controller\Router', [
            'urlHelper' => $this->_urlHelper,
            'urlParser' => $this->_urlParser
        ]);

        $this->_request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->setMethods(['getPathInfo'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestReflector = new \ReflectionProperty('\Digidirect\LayeredNavigation\Helper\Url', '_request');
        $requestReflector->setAccessible(true);
        $requestReflector->setValue($this->_urlHelper, $this->_request);
    }

    /**
     * Test case for match SEO request
     * @return void
     */
    public function testMatchSeoAttribute()
    {
        $requestUrl = 'some/catalog/category/filters/color/red,blue,green/size/xs,m.html';
        $this->_request->setParams([]);
        $this->_request->setModuleName('catalog');
        $this->_request->setControllerName('category');
        $this->_request->setActionName('view');

        $this->_request->expects($this->any())
            ->method('getPathInfo')
            ->willReturn($requestUrl);

        $this->_request->expects($this->any())
            ->method('getRouteName')
            ->willReturn('catalog');

        $this->_urlHelper->expects($this->any())
            ->method('isSeoUrlEnabled')
            ->willReturn(true);

        $this->_scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->with('digidirect_layerednavigation/url/mode', 'website')
            ->willReturn(true);

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('catalog/seo/category_url_suffix')
            ->willReturn('.html');

        $this->_seoHelper->expects($this->any())
            ->method('parseRequestUri')
            ->with('catalog/seo/category_url_suffix')
            ->willReturn([
                $requestUrl,
                'some/catalog/category',
                'color/red,blue,green/size/xs,m.html'
            ]);

        $this->_urlHelper->expects($this->any())
            ->method('removeCategorySuffix')
            ->willReturn('color/red,blue,green/size/m,xs');

        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn([
                10 => [
                    'alias' => 'red',
                    'attribute_code' => 'color',
                ],
                11 => [
                    'alias' => 'green',
                    'attribute_code' => 'color',
                ],
                12 => [
                    'alias' => 'blue',
                    'attribute_code' => 'color',
                ],
                13 => [
                    'alias' => 'xs',
                    'attribute_code' => 'size',
                ],
                14 => [
                    'alias' => 'm',
                    'attribute_code' => 'size',
                ],
            ]);

        $expectedResult = [
            'size' => '14,13',
            'color' => '10,12,11',
        ];

        $this->_router->match($this->_request);
        $this->assertEquals($expectedResult, $this->_request->getParams());
    }
}
