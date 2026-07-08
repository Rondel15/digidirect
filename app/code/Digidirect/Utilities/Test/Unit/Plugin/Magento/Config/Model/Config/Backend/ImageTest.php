<?php
namespace Digidirect\Utilities\Test\Unit\Plugin\Magento\Config\Model\Config\Backend;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    const LOADING_ANIMATION_IMAGE_PATH = 'design/loading_animation/image';

    /**
     * Image plugin object
     * @var \Digidirect\Utilities\Plugin\Magento\Config\Model\Config\Backend\Image | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_imagePlugin;

    /**
     * Image Plugin Object reflection
     * @var \ReflectionClass
     */
    protected $_imagePluginReflector;

    /**
     * Set up required objects
     * @return void
     */

    /**
     * Request object
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    /**
     * Set up reused objects
     * @return void
     */
    public function setUp()
    {
        $this->_request = $this->getMockBuilder('\Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_imagePlugin = $this->getMockBuilder('\Digidirect\Utilities\Plugin\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['beforeBeforeSave'])
            ->getMock();

        $this->_imagePluginReflector = new \ReflectionClass(
            '\Digidirect\Utilities\Plugin\Magento\Config\Model\Config\Backend\Image'
        );
    }

    /**
     * Case when enable first time without uploaded image
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage You have not uploaded any images yet
     * @return void
     */
    public function testFirstEnablingWithoutImage()
    {
        $subject = $this->getMockBuilder('\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getValue', 'getOldValue'])
            ->getMock();

        $subject->expects($this->any())
            ->method('getPath')
            ->willReturn(self::LOADING_ANIMATION_IMAGE_PATH);

        $subject->expects($this->any())
            ->method('getValue')
            ->willReturn([
                'delete' => 0,
                'name' => '',
                'value' => ''
            ]);

        $subject->expects($this->any())
            ->method('getOldValue')
            ->willReturn('');

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('groups')
            ->willReturn([
                'loading_animation' => [
                    'fields' => [
                        'enable' => 1
                    ]
                ]
            ]);

        $reflectionProperty = $this->_imagePluginReflector->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_imagePlugin, $this->_request);

        $method = $this->_imagePluginReflector->getMethod('beforeBeforeSave');
        $this->assertNull($method->invokeArgs($this->_imagePlugin, [$subject]));
    }

    /**
     * Case when enable first time without uploaded image
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Please upload an image
     * @return void
     */
    public function testSaveEnabledWithoutImage()
    {
        $subject = $this->getMockBuilder('\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getValue', 'getOldValue'])
            ->getMock();

        $subject->expects($this->any())
            ->method('getPath')
            ->willReturn(self::LOADING_ANIMATION_IMAGE_PATH);

        $subject->expects($this->any())
            ->method('getValue')
            ->willReturn([
                'delete' => 0,
                'name' => 'newImage',
                'value' => ''
            ]);

        $subject->expects($this->any())
            ->method('getOldValue')
            ->willReturn('11');

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('groups')
            ->willReturn([
                'loading_animation' => [
                    'fields' => [
                        'enable' => 1
                    ]
                ]
            ]);

        $reflectionProperty = $this->_imagePluginReflector->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_imagePlugin, $this->_request);

        $method = $this->_imagePluginReflector->getMethod('beforeBeforeSave');
        $this->assertNull($method->invokeArgs($this->_imagePlugin, [$subject]));
    }

    /**
     * Test case when ticked "delete image" and uploaded new image
     * @return void
     */
    public function testSaveWithDeletingImageWithNewImage()
    {

        $subject = $this->getMockBuilder('\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getValue', 'getOldValue'])
            ->getMock();

        $subject->expects($this->any())
            ->method('getPath')
            ->willReturn(self::LOADING_ANIMATION_IMAGE_PATH);

        $subject->expects($this->any())
            ->method('getValue')
            ->willReturn([
                'delete' => 1,
                'name' => 'newImage'
            ]);

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('groups')
            ->willReturn([
                'loading_animation' => [
                    'fields' => [
                        'enable' => 1
                    ]
                ]
            ]);

        $reflectionProperty = $this->_imagePluginReflector->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_imagePlugin, $this->_request);

        $method = $this->_imagePluginReflector->getMethod('beforeBeforeSave');
        $this->assertNull($method->invokeArgs($this->_imagePlugin, [$subject]));
    }

    /**
     * Test case when ticked "delete image" and not uploaded new
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage You have to upload new image when remove the old one
     * @return void
     */
    public function testSaveWithDeletingImageWithoutNewImage()
    {
        $subject = $this->getMockBuilder('\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPath', 'getValue', 'getOldValue'])
            ->getMock();

        $subject->expects($this->any())
            ->method('getPath')
            ->willReturn(self::LOADING_ANIMATION_IMAGE_PATH);

        $subject->expects($this->any())
            ->method('getValue')
            ->willReturn([
                'delete' => 1,
                'name' => ''
            ]);

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('groups')
            ->willReturn([
                'loading_animation' => [
                    'fields' => [
                        'enable' => 1
                    ]
                ]
            ]);

        $reflectionProperty = $this->_imagePluginReflector->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_imagePlugin, $this->_request);

        $method = $this->_imagePluginReflector->getMethod('beforeBeforeSave');
        $method->invokeArgs($this->_imagePlugin, [$subject]);
    }

    /**
     * Test case when loading animation is disabled
     * @return void
     */
    public function testSaveDisabled()
    {
        $subject = $this->getMockBuilder('\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPath'])
            ->getMock();

        $subject->expects($this->any())
            ->method('getPath')
            ->willReturn(self::LOADING_ANIMATION_IMAGE_PATH);

        $this->_request->expects($this->any())
            ->method('getParam')
            ->with('groups')
            ->willReturn([
                'loading_animation' => [
                    'fields' => [
                        'enable' => 0
                    ]
                ]
            ]);

        $reflectionProperty = $this->_imagePluginReflector->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_imagePlugin, $this->_request);

        $method = $this->_imagePluginReflector->getMethod('beforeBeforeSave');
        $this->assertEquals(null, $method->invokeArgs($this->_imagePlugin, [$subject]));

    }

    /**
     * Save information/image from input type "image" - not from utilities module
     * @return void
     */
    public function testNotLoadingAnimationImagePath()
    {
        $subject = $this->getMockBuilder('\Magento\Config\Model\Config\Backend\Image')
            ->disableOriginalConstructor()
            ->setMethods(['getPath'])
            ->getMock();

        $subject->expects($this->any())
            ->method('getPath')
            ->willReturn('non/loading_animation/path');

        $reflectionProperty = $this->_imagePluginReflector->getProperty('_request');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->_imagePlugin, $this->_request);

        $this->_request->expects($this->never())
            ->method('getParam')
            ->with('groups')
            ->willReturn(null);

        $method = $this->_imagePluginReflector->getMethod('beforeBeforeSave');
        $this->assertEquals(null, $method->invokeArgs($this->_imagePlugin, [$subject]));
    }
}
