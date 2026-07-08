<?php
namespace Digidirect\SEO\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AbstractTest
 * @package Digidirect\SEO\Test\Unit
 */
class AbstractTestUnit extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup object manager
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Create new mock object without constructor
     *
     * @param string $className
     * @param [] $methods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockObjectWithoutConstructor(string $className, array $methods = [])
    {
        return $this->getMockBuilder($className)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get reflection class
     *
     * @param string $className
     * @return \ReflectionClass
     */
    protected function getReflectionClass(string $className)
    {
        return new \ReflectionClass($className);
    }
}
