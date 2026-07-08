<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Helper;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Url helper mock
     * @var \Digidirect\LayeredNavigation\Helper\Url
     */
    protected $_urlHelper;

    /**
     * Scope config mock
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * Seo helper (data) mock
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_seoHelper;

    /**
     * Request mock
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * Set up required common objects
     * @return void
     */
    public function setUp()
    {
        $reflector = new \ReflectionClass('\Digidirect\LayeredNavigation\Helper\Url');
        $this->_urlHelper = $reflector->newInstanceWithoutConstructor();

        $this->_scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $property = $reflector->getProperty('scopeConfig');
        $property->setAccessible(true);
        $property->setValue($this->_urlHelper, $this->_scopeConfig);

        $this->_seoHelper = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\Data')
            ->disableOriginalConstructor()
            ->setMethods([
                'getOptionsSeoData',
                'getSeoSignificantUrlParameters',
                'isEnabled',
                'getSuffix'
            ])
            ->getMock();

        $property = $reflector->getProperty('helper');
        $property->setAccessible(true);
        $property->setValue($this->_urlHelper, $this->_seoHelper);

        $seoHelperReflector = new \ReflectionClass('\Digidirect\LayeredNavigation\Helper\Url');
        $moduleManager = $seoHelperReflector->getProperty('moduleManager');
        $moduleManager->setAccessible(true);
        $moduleManager->setValue(
            $this->_urlHelper,
            $this->getMockBuilder('\Magento\Framework\Module\Manager')
                ->disableOriginalConstructor()
                ->getMock()
        );

        $this->_request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->setMethods(['getControllerName', 'getActionName', 'getModuleName'])
            ->getMock();

        $requestProperty = $seoHelperReflector->getProperty('_request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_urlHelper, $this->_request);
    }

    /**
     * Test case catalog search url
     * need to be seofying
     * @return void
     */
    public function testSeofyUrlCatalog()
    {
        $catalogFilteredUrl =
            'http://test.com/index.php/collections/yoga-new.html?climate=218&product_list_order=name';

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('catalog/seo/category_url_suffix')
            ->willReturn('.html');

        $optionsDataArray = [
            218 => [
                'alias' => 'wintry',
                'attribute_code' => 'climate'
            ]
        ];

        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn($optionsDataArray);

        $this->_seoHelper->expects($this->any())
            ->method('getSuffix')
            ->willReturn('.html');

        $seoSignificantParameters = [
            'size',
            'color',
            'price',
            'climate',
            'category_gear',
            'style_bags',
            'category_ids'
        ];
        $this->_seoHelper->expects($this->any())
            ->method('getSeoSignificantUrlParameters')
            ->willReturn($seoSignificantParameters);

        $this->_seoHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn(1);

        $this->assertEquals(
            'http://test.com/index.php/collections/yoga-new/filters/climate/wintry.html?product_list_order=name',
            $this->_urlHelper->seofyUrl($catalogFilteredUrl)
        );
    }

    /**
     * Test case catalog search url
     * need to be seofying
     * @return void
     */
    public function testSeofyUrlCatalogSearch()
    {
        $catalogSearchFilteredUrl =
            'http://test.com/index.php/catalogsearch/result/index/?climate=221%2C220%2C219&q=top';

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('catalog/seo/category_url_suffix')
            ->willReturn('.html');

        $optionsDataArray = [
            219 => [
                'alias' => 'wintry',
                'attribute_code' => 'climate'
            ],
            220 => [
                'alias' => 'windy',
                'attribute_code' => 'climate'
            ],
            221 => [
                'alias' => 'hot',
                'attribute_code' => 'climate'
            ]
        ];

        $this->_request->expects($this->any())
            ->method('getActionName')
            ->willReturn('index');

        $this->_request->expects($this->any())
            ->method('getControllerName')
            ->willReturn('result');

        $this->_request->expects($this->any())
            ->method('getModuleName')
            ->willReturn('catalogsearch');

        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn($optionsDataArray);

        $seoSignificantParameters = [
            'size',
            'color',
            'price',
            'climate',
            'category_gear',
            'style_bags',
            'category_ids'
        ];
        $this->_seoHelper->expects($this->any())
            ->method('getSeoSignificantUrlParameters')
            ->willReturn($seoSignificantParameters);

        $this->_seoHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn(1);

        $this->assertEquals(
            'http://test.com/index.php/catalogsearch/result/index/filters/climate/hot,windy,wintry?q=top',
            $this->_urlHelper->seofyUrl($catalogSearchFilteredUrl)
        );
    }

    /**
     * Test case for third module
     * no modify url
     * @return void
     */
    public function testSeofyUrlThirdModule()
    {
        $customModuleUrl =
            'http://test.com/index.php/custom/custom/custom';

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('catalog/seo/category_url_suffix')
            ->willReturn('.html');

        $optionsDataArray = [
            219 => [
                'alias' => 'wintry',
                'attribute_code' => 'climate'
            ],
            220 => [
                'alias' => 'windy',
                'attribute_code' => 'climate'
            ],
            221 => [
                'alias' => 'hot',
                'attribute_code' => 'climate'
            ]
        ];

        $this->_request->expects($this->any())
            ->method('getActionName')
            ->willReturn('custom');

        $this->_request->expects($this->any())
            ->method('getControllerName')
            ->willReturn('custom');

        $this->_request->expects($this->any())
            ->method('getModuleName')
            ->willReturn('custom');

        $this->_seoHelper->expects($this->any())
            ->method('getOptionsSeoData')
            ->willReturn($optionsDataArray);

        $seoSignificantParameters = [
            'size',
            'color',
            'price',
            'climate',
            'category_gear',
            'style_bags',
            'category_ids'
        ];
        $this->_seoHelper->expects($this->any())
            ->method('getSeoSignificantUrlParameters')
            ->willReturn($seoSignificantParameters);

        $this->_seoHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn(1);

        $this->assertEquals(
            $customModuleUrl,
            $this->_urlHelper->seofyUrl($customModuleUrl)
        );
    }
}
