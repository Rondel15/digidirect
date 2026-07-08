<?php
namespace Digidirect\SEO\Test\Unit\Model;

use Digidirect\SEO\Model\SitemapExclude;
use Digidirect\SEO\Model\ResourceModel\SitemapExclude as SitemapExcludeResource;
use Digidirect\SEO\Model\SitemapExcludeFactory;
use Digidirect\SEO\Model\SitemapExcludeRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class SitemapExcludeRepositoryTest
 * @package Digidirect\SEO\Test\Unit\Model
 */
class SitemapExcludeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapExcludeResource
     */
    private $resourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapExcludeFactory
     */
    private $sitemapExcludeFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapExcludeResource\CollectionFactory
     */
    private $sitemapExcludeCollectionFactoryMock;

    /**
     * @var SitemapExcludeRepository
     */
    private $repository;

    /**
     * Initialize repository
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMock(SitemapExcludeResource::class, [], [], '', false);
        $this->sitemapExcludeFactoryMock = $this->getMock(SitemapExcludeFactory::class, ['create'], [], '', false);
        $this->sitemapExcludeCollectionFactoryMock = $this->getMock(
            SitemapExcludeResource\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $objectManager = new ObjectManager($this);
        $this->repository = $objectManager->getObject(
            SitemapExcludeRepository::class,
            [
                'resource' => $this->resourceMock,
                'sitemapExcludeFactory' => $this->sitemapExcludeFactoryMock,
                'sitemapExcludeCollectionFactory' =>  $this->sitemapExcludeCollectionFactoryMock
            ]
        );
    }

    /**
     * @test
     */
    public function testSave()
    {
        $sitemapExclude = $this->getMock(SitemapExclude::class, [], [], '', false);
        $this->resourceMock->expects($this->once())->method('save')->willReturnSelf();
        $this->assertEquals($sitemapExclude, $this->repository->save($sitemapExclude));
    }

    /**
     * @test
     * @dataProvider getByItemIdProvider
     * @param int|null$excludeId
     */
    public function testGetByItemId($excludeId)
    {
        $excludedItemId = 2;
        $storeId = 1;
        $sitemapExclude = $this->getMock(SitemapExclude::class, [], [], '', false);
        $this->sitemapExcludeFactoryMock->expects($this->any())->method('create')->willReturn($sitemapExclude);
        $this->resourceMock->expects($this->any())->method('loadByItemId')->willReturn($sitemapExclude);
        $sitemapExclude->expects($this->once())->method('getId')->willReturn($excludeId);
        if ($excludeId) {
            $this->assertInstanceOf(
                SitemapExclude::class,
                $this->repository->getByItemId(SitemapExclude::ITEM_TYPE_CATEGORY, $excludedItemId, $storeId)
            );
        } else {
            $this->setExpectedException(\Magento\Framework\Exception\NoSuchEntityException::class);
            $this->repository->getByItemId(SitemapExclude::ITEM_TYPE_CATEGORY, $excludedItemId, $storeId);
        }
    }

    /**
     * Provider for getByItemId method
     *
     * @return array
     */
    public function getByItemIdProvider()
    {
        return [[10], [null]];
    }

    /**
     * @test
     */
    public function testGetExcludedItemIds()
    {
        $excludedIds = [2, 3, 5];
        $storeId = 1;
        $collection = $this->getMock(SitemapExcludeResource\Collection::class, [], [], '', false);
        $collection->expects($this->once())->method('getExcludedItemIds')->willReturn($excludedIds);
        $this->sitemapExcludeCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $this->assertEquals(
            $excludedIds,
            $this->repository->getExcludedItemIds(SitemapExclude::ITEM_TYPE_CATEGORY, $storeId)
        );
    }
}
