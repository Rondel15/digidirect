<?php
namespace Digidirect\CheckoutFields\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class TestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * Setup object manager
     */
    public function setUp()
    {
        $this->_objectManager = new ObjectManager($this);
    }

    /**
     * Get mock object without constructor
     *
     * @param string $className
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObjectWithoutConstructor($className, array $methods = [])
    {
        return $this->getMockBuilder($className)->disableOriginalConstructor()->setMethods($methods)->getMock();
    }

    /**
     * Get mock object with constructor
     *
     * @param string $className
     * @param array $constructorArgs
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockObjectWithConstructor($className, array $constructorArgs = [], array $methods = [])
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->setConstructorArgs($constructorArgs)
            ->getMock();
    }

    /**
     * @param array $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getScopeConfigMock(array $methods = [])
    {
        return $this->getMockObjectWithoutConstructor('Magento\Framework\App\Config', $methods);
    }

    /**
     * Create reflection class
     *
     * @param string $className
     * @return \ReflectionClass
     */
    protected function _createReflection($className)
    {
        return new \ReflectionClass($className);
    }

    /**
     * Create real object without constructor
     *
     * @param string $className
     * @return object
     */
    protected function getRealObjectWithoutConstructor($className)
    {
        return $this->_createReflection($className)->newInstanceWithoutConstructor();
    }
}
