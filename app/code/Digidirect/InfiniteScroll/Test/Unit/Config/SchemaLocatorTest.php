<?php

namespace Digidirect\InfiniteScroll\Test\Unit\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\SchemaLocator
     */
    protected $model;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolver;

    /** @var \Magento\Framework\Config\Dom\UrnResolver $urnResolverMock */
    protected $urnResolverMock;

    protected function setUp()
    {
        $this->urnResolver = new \Magento\Framework\Config\Dom\UrnResolver();
        $this->urnResolverMock = $this->getMock('Magento\Framework\Config\Dom\UrnResolver', [], [], '', false);
        $this->model = new \Digidirect\InfiniteScroll\Model\Config\SchemaLocator($this->urnResolverMock);
    }

    public function testGetSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd'),
            $this->model->getSchema()
        );
    }

    public function testGetPerFileSchema()
    {
        $this->urnResolverMock->expects($this->once())
            ->method('getRealPath')
            ->with('urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd')
            ->willReturn(
                $this->urnResolver->getRealPath('urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd')
            );
        $this->assertEquals(
            $this->urnResolver->getRealPath('urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd'),
            $this->model->getPerFileSchema()
        );
    }
}
