<?php
namespace Digidirect\Blog\Test\Unit\Model;

use Digidirect\Utilities\Test\Unit\Library;
use Magento\Framework\DataObject;

/**
 * Class UrlModelTest
 */
class UrlModelTest extends Library
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Digidirect\Blog\Model\UrlModel
     */
    protected $urlModel;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->dataHelperMock = $this->getMockObjectWithoutConstructor(
            'Digidirect\Blog\Helper\Data',
            ['getGeneralSettingsConfig']
        );
        $this->urlBuilderMock = $this->getMockObjectWithoutConstructor('Magento\Framework\Url', ['getUrl']);

        $this->urlModel = $this->objectManager->getObject(
            'Digidirect\Blog\Model\UrlModel',
            [
                'dataHelper' => $this->dataHelperMock,
                'urlBuilder' => $this->urlBuilderMock
            ]
        );
    }

    /**
     * Test prepare url if the same url key is exists
     */
    public function testPrepareUrlKey()
    {
        $mockModel = $this->getMockObjectWithoutConstructor(
            'Digidirect\Blog\Model\Post',
            ['getData', 'getCollection', 'getId']
        );
        $mockModel->method('getData')->willReturn('Test Post');
        $mockModel->method('getId')->willReturn(null);
        $collectionMock = $this->getMockObjectWithoutConstructor(
            'Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection',
            ['addFieldToFilter', 'setPageSize', 'getFirstItem', 'getLastItem']
        );
        $firstItemObject = new DataObject();
        $lastItemItemObject = new DataObject();
        $firstItemObject->setId(1);
        $lastItemItemObject->setId(2);
        $collectionMock->method('addFieldToFilter')->willReturnSelf();
        $collectionMock->method('setPageSize')->willReturnSelf();
        $collectionMock->method('getFirstItem')->willReturn($firstItemObject);
        $collectionMock->method('getLastItem')->willReturn($lastItemItemObject);
        $mockModel->method('getCollection')->willReturn($collectionMock);
        $urlKey = $this->urlModel->prepareUrlKey($mockModel, 'title');
        $this->assertEquals('test-post-3', $urlKey);
    }

    /**
     *  Test process Url Key
     */
    public function testProcessUrlKey()
    {
        $this->assertEquals('test-post', $this->urlModel->processUrlKey('Test Post'));
    }
}
