<?php

namespace Digidirect\AbstractEntity\Test\Unit\Model;

use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as ResourceAbstractEntity;
use Digidirect\AbstractEntity\Helper\Config as ConfigHelper;

class AbstractEntityRepositoryTest extends \PHPUnit\Framework\TestCase
{
    const INDEX_TABLE = 'index';
    const EAV_TABLE = 'eav';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $aeRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configHelper;

    /**
     * Set up required common objects
     *
     * @return void
     */
    public function setUp()
    {
        $this->initResource();
        $this->initConfigHelper();
        $this->initAERepository();
    }

    /**
     * @return array
     */
    public function dataTestGetCollection()
    {
        return [
            [ null, false, self::EAV_TABLE],
            [ null, true, self::EAV_TABLE],
            [ 1, false, self::EAV_TABLE],
            [ 1, true, self::INDEX_TABLE],
        ];
    }

    /**
     * @dataProvider dataTestGetCollection
     * @param int $setId
     * @param bool $isIndexTableEnableForEntity
     * @param string $expectedResult
     */
    public function testGetCollection($setId, $isIndexTableEnableForEntity, $expectedResult)
    {
        $expects = $this->once();
        if ($setId === null) {
            $expects = $this->never();
        }
        $this->configHelper->expects($expects)
            ->method('isIndexTableEnableForEntity')
            ->willReturn($isIndexTableEnableForEntity);

        $this->resource->expects($this->once())
            ->method('getAttributeSetIdByName')
            ->willReturn($setId);

        $this->assertEquals($expectedResult, $this->aeRepository->getCollection());

    }

    /**
     * @return void
     */
    protected function initAERepository()
    {
        $this->aeRepository = $this->getMockBuilder(AbstractEntityRepository::class)
            ->setMethods(
                [
                    '_getIndexCollection',
                    '_getEavCollection'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->aeRepository->expects($this->any())
            ->method('_getIndexCollection')
            ->willReturn(self::INDEX_TABLE);

        $this->aeRepository->expects($this->any())
            ->method('_getEavCollection')
            ->willReturn(self::EAV_TABLE);

        $this->_setProtectedProperty($this->aeRepository, 'resource', $this->resource);
        $this->_setProtectedProperty($this->aeRepository, 'configHelper', $this->configHelper);
    }

    /**
     * @return void
     */
    protected function initResource()
    {
        $this->resource = $this->getMockBuilder(ResourceAbstractEntity::class)
            ->setMethods(
                [
                    'getAttributeSetIdByName',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return void
     */
    protected function initConfigHelper()
    {
        $this->configHelper = $this->getMockBuilder(ConfigHelper::class)
            ->setMethods(
                [
                    'isIndexTableEnableForEntity',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     */
    protected function _setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass($object);
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
