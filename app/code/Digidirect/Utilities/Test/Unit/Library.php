<?php
namespace Digidirect\Utilities\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class Library
 * Useful methods for unit testing
 */
class Library extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Initialize object manager
     *
     * @return void
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Get mock object with disabled constructor
     *
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
     * Get reflection object for specified class to manipulate data
     *
     * @param string $className
     * @return \ReflectionClass
     */
    protected function getReflectionClass($className)
    {
        return new \ReflectionClass($className);
    }

    /**
     * @param \ReflectionClass $class
     * @param string $propertyName
     * @return \ReflectionProperty
     */
    protected function setAccessibleProtectedProperty(\ReflectionClass $class, $propertyName)
    {
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * @param \ReflectionClass $class
     * @param string $methodName
     * @return \ReflectionMethod
     */
    protected function setAccessibleProtectedMethod(\ReflectionClass $class, $methodName)
    {
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
