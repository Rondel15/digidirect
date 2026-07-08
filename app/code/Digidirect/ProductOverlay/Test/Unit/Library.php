<?php
namespace Digidirect\ProductOverlay\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Library
 * @package Digidirect\ProductOverlay\Test\Unit
 */
class Library extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup object manager
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param string $className
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObjectWithoutConstructor($className, array $methods = [])
    {
        return $this->getMockBuilder($className)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $className
     * @return \ReflectionClass
     */
    protected function getReflectionClass($className)
    {
        return new \ReflectionClass($className);
    }

    /**
     * @param \ReflectionClass $class
     * @param string $property
     * @return \ReflectionProperty
     */
    protected function setAccessibleProperty(\ReflectionClass $class, $property)
    {
        $property = $class->getProperty($property);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * @param \ReflectionClass $class
     * @param string $methodName
     * @return \ReflectionMethod
     */
    protected function setAccessibleMethod(\ReflectionClass $class, $methodName)
    {
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
