<?php
namespace Digidirect\Utilities\Test\Unit\Preference\Framework\View\Result;

use Zend\Server\Reflection\ReflectionClass;
use Zend\Server\Reflection\ReflectionMethod;

class PageTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Scope config interface
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * Animation Helper Mock Object
     * @var \Digidirect\Utilities\Helper\Animation| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_animationHelper;

    /**
     * Page Mock Object
     * @var \Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_page;

    /**
     * Set up parameters
     * @return void
     */
    public function setUp()
    {
        $this->_scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_page = $this->getMockBuilder(
            '\Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page'
        )
            ->disableOriginalConstructor()
            ->setMethods(['_getLoader'])
            ->getMock();
    }

    /**
     * Test case when system/configuration animation is disabled
     * @return void
     */
    public function testDisabled()
    {

        $this->_scopeConfig
            ->method('getValue')
            ->with('design/loading_animation/enabled')
            ->willReturn(false);

        $reflector = new \ReflectionClass('\Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page');

        $animationHelper = $this->getMockBuilder('Digidirect\Utilities\Helper\Animation')
            ->disableOriginalConstructor()
            ->getMock();

        $animationHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn(0);

        $utilitiesProperty = $reflector->getProperty('_utilitiesHelper');
        $utilitiesProperty->setAccessible(true);
        $utilitiesProperty->setValue($this->_page, $animationHelper);

        $animationHelper = $this->getMockBuilder('Framework\App\Request\Http')
            ->setMethods(['isSecure', 'getUrlWithParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestProperty = $reflector->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_page, $animationHelper);

        $animationHelper = $this->getMockBuilder('\Magento\Framework\View\Asset\Repository')
            ->setMethods(['getUrlWithParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $animationHelper->expects($this->once())->method('getUrlWithParams')
            ->with('images/loader-2.gif')
            ->willReturn('url/original_file');

        $requestProperty = $reflector->getProperty('assetRepo');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_page, $animationHelper);

        $method = $reflector->getMethod('_getLoader');
        $method->setAccessible(true);

        $r = $method->invokeArgs($this->_page, []);
        $this->assertEquals('url/original_file', $r);
    }

    /**
     * Test case if animation is enabled and image path is exists in stores/configuration
     * @param string $expectedImage
     * @dataProvider enableExistImage
     * @return void
     */
    public function testEnabled($expectedImage)
    {
        $expectedImageUrl = 'animation/' . $expectedImage;

        $reflector = new \ReflectionClass('\Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page');

        $animationHelper = $this->getMockBuilder('Digidirect\Utilities\Helper\Animation')
            ->disableOriginalConstructor()
            ->getMock();

        $animationHelper->expects($this->any())
            ->method('isEnabled')
            ->willReturn(1);

        $animationHelper->expects($this->any())
            ->method('getImage')
            ->willReturn($expectedImageUrl);

        $utilitiesProperty = $reflector->getProperty('_utilitiesHelper');
        $utilitiesProperty->setAccessible(true);
        $utilitiesProperty->setValue($this->_page, $animationHelper);

        $method = $reflector->getMethod('_getLoader');
        $method->setAccessible(true);
        $this->assertEquals($expectedImageUrl, $method->invokeArgs($this->_page, []));
    }

    /**
     * Test case when animation is enabled but image was not uploaded
     * @return void
     */
    public function testEnabledWithoutImage()
    {
        $this->_scopeConfig
            ->method('getValue')
            ->with('design/loading_animation/enabled')
            ->willReturn(true);

        $reflector = new \ReflectionClass('\Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page');

        $animationHelper = $this->getMockBuilder('Digidirect\Utilities\Helper\Animation')
            ->disableOriginalConstructor()
            ->getMock();

        $animationHelper->expects($this->once())
            ->method('isEnabled')
            ->willReturn(1);

        $animationHelper->expects($this->any())
            ->method('getImage')
            ->willReturn(null);

        $utilitiesProperty = $reflector->getProperty('_utilitiesHelper');
        $utilitiesProperty->setAccessible(true);
        $utilitiesProperty->setValue($this->_page, $animationHelper);

        $animationHelper = $this->getMockBuilder('Framework\App\Request\Http')
            ->setMethods(['isSecure', 'getUrlWithParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestProperty = $reflector->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_page, $animationHelper);

        $animationHelper = $this->getMockBuilder('\Magento\Framework\View\Asset\Repository')
            ->setMethods(['getUrlWithParams'])
            ->disableOriginalConstructor()
            ->getMock();

        $animationHelper->expects($this->once())->method('getUrlWithParams')
            ->with('images/loader-2.gif')
            ->willReturn('url/original_file');

        $requestProperty = $reflector->getProperty('assetRepo');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->_page, $animationHelper);

        $method = $reflector->getMethod('_getLoader');
        $method->setAccessible(true);

        $r = $method->invokeArgs($this->_page, []);
        $this->assertEquals('url/original_file', $r);
    }

    /**
     * Data provider
     * @return []
     */
    public function enableExistImage()
    {
        return [['existImage1'], ['existImage2']];
    }
}
