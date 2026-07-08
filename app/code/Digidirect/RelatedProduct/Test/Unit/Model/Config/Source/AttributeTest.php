<?php
namespace Digidirect\RelatedProduct\Test\Unit\Model\Config\Source;

use Digidirect\RelatedProduct\Test\Unit\RelatedProductTestUnitTrait;
use Digidirect\RelatedProduct\Model\Config\Source\Attribute;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\DataObject;

/**
 * Class RelatedTest
 * @package Digidirect\RelatedProduct\Test\Unit\Block\ProductList
 */
class AttributeTest extends \PHPUnit_Framework_TestCase
{
    use RelatedProductTestUnitTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_searchCriteriaMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_attributeSearchResults;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filterBuilderMock;

    /**
     * Setup objects
     * @return void
     */
    public function setUp()
    {
        $this->_attributeRepositoryMock = $this->getMockObjectWithoutConstructor(
            AttributeRepository::class,
            ['getList']
        );

        $this->_attributeSearchResults = $this->getMockObjectWithoutConstructor(
            SearchResults::class,
            ['getItems']
        );

        $this->_searchCriteriaMock = $this->getMockObjectWithoutConstructor(
            SearchCriteria::class,
            []
        );

        $this->_searchCriteriaBuilderMock = $this->getMockObjectWithoutConstructor(
            SearchCriteriaBuilder::class,
            ['create', 'addFilters']
        );
        $this->_searchCriteriaBuilderMock->expects($this->once())
            ->method('addFilters')
            ->willReturn($this->_searchCriteriaBuilderMock);
        $this->_filterBuilderMock = $this->getMockObjectWithoutConstructor(
            FilterBuilder::class,
            ['create']
        );
        $this->_searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_searchCriteriaMock);

        $this->_attributeModelMock = $this->getMockObjectWithConstructor(
            Attribute::class,
            null,
            [
                $this->_attributeRepositoryMock,
                $this->_searchCriteriaBuilderMock,
                $this->_filterBuilderMock
            ]
        );
    }

    /**
     * @param $data
     * @param $expected
     * @dataProvider provideOptionAttributes
     */
    public function testGetOptionAttributes($data, $expected)
    {
        $this->_attributeSearchResults->expects($this->once())
            ->method('getItems')
            ->willReturn($data);

        $this->_attributeRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn($this->_attributeSearchResults);

        $result = $this->_attributeModelMock->getOptionAttributes();

        $this->assertEquals($expected, $result[0]['value']);
    }

    /**
     * @return array
     */
    public function provideOptionAttributes()
    {
        return [
            [
                [new DataObject(['frontend_label' => 'Color', 'attribute_code' => 'color'])],
                'color'
            ],
            [
                [new DataObject([])],
                ''
            ]
        ];
    }
}
