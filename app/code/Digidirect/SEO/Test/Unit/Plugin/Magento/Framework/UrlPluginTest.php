<?php

namespace Digidirect\SEO\Test\Unit\Plugin\Magento\Framework;

use Digidirect\SEO\Test\Unit\AbstractTestUnit;
use Digidirect\SEO\Plugin\Magento\Framework\UrlInterfacePlugin;
use Magento\Framework\Url;
use Digidirect\SEO\Helper\TrailingSlash as TrailingSlashHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UrlPluginTest extends AbstractTestUnit
{

    /**
     * @var TrailingSlashHelper
     */
    protected $helperMock;

    /**
     * @var UrlInterfacePlugin
     */
    protected $plugin;

    /**
     * @var Url
     */
    protected $pluginSubjectReflect;

    /**
     * Setup object manager
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $om = new ObjectManager($this);

        $this->helperMock = $this->getMockObjectWithoutConstructor(
            TrailingSlashHelper::class,
            ['isTrailingSlashEnabled']
        );

        $this->plugin = $om->getObject(
            UrlInterfacePlugin::class,
            ['helper' => $this->helperMock]
        );

        $this->pluginSubjectReflect = $om->getObject(
            Url::class
        );
    }

    /**
     * @dataProvider provideData
     * @param $data
     * @param $expected
     */
    public function testCutTrailingSlash($data, $expected)
    {
        $this->helperMock->expects($this->any())
            ->method('isTrailingSlashEnabled')
            ->willReturn(false);

        $this->assertEquals($expected, $this->plugin->afterGetUrl($this->pluginSubjectReflect, $data));
    }

    /**
     * @dataProvider provideData
     * @param $data
     */
    public function testWithoutCutTrailingSlash($data)
    {
        $this->helperMock->expects($this->any())
            ->method('isTrailingSlashEnabled')
            ->willReturn(true);

        $this->assertEquals($data, $this->plugin->afterGetUrl($this->pluginSubjectReflect, $data));
    }

    /**
     * Provide processed objects
     *
     * @return []
     */
    public function provideData()
    {
        return [
            [
                'http://test.com/test.html/',
                'http://test.com/test.html'
            ],
            [
                'http://test.com/test/',
                'http://test.com/test'
            ],
            [
                'http://test.com/test.html#hash/',
                'http://test.com/test.html#hash'
            ],
        ];
    }

    /**
     * Set accessible protected/private property
     *
     * @param \ReflectionClass $class
     * @param $propertyName
     * @return \ReflectionProperty
     */
    protected function setAccessibleProperty($class, $propertyName)
    {
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        return $property;
    }
}
