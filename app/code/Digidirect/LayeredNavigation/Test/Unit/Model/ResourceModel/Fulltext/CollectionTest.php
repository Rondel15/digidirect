<?php
namespace Digidirect\LayeredNavigation\Test\Unit\Model\ResourceModel\Fulltext;

use Magento\Framework\Search\Request\Aggregation\TermBucket;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Request\Dimension;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection
     */
    protected $_collection;

    /**
     * @var \Digidirect\LayeredNavigation\Model\Request\Builder
     */
    protected $_requestBuilder;

    /**
     * @var \Magento\Search\Model\SearchEngine
     */
    protected $_searchEngine;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\Data
     */
    protected $_helper;

    /**
     * Set up required common objects
     * @return void
     */
    public function setUp()
    {
        $reflectionOfCollection = new \ReflectionClass(
            '\Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection'
        );
        $this->_collection = $reflectionOfCollection->newInstanceWithoutConstructor();

        $storeIdReflector = $reflectionOfCollection->getProperty('_storeId');
        $storeIdReflector->setAccessible(true);
        $storeIdReflector->setValue($this->_collection, 1);

        $isFiltersRenderedReflector = $reflectionOfCollection->getProperty('_isFiltersRendered');
        $isFiltersRenderedReflector->setAccessible(true);
        $isFiltersRenderedReflector->setValue($this->_collection, true);

        $this->_requestBuilder = $this->getMockBuilder('\Digidirect\LayeredNavigation\Model\Request\Builder')
            ->setMethods(['autoSetRequestName', 'create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_helper = $this->getMockBuilder('\Digidirect\LayeredNavigation\Helper\Data')
            ->setMethods(['isApplyButtonEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $helperReflector = $reflectionOfCollection->getProperty('helper');
        $helperReflector->setAccessible(true);
        $helperReflector->setValue($this->_collection, $this->_helper);

        $requestBuilderReflector = $reflectionOfCollection->getProperty('requestBuilder');
        $requestBuilderReflector->setAccessible(true);
        $requestBuilderReflector->setValue($this->_collection, $this->_requestBuilder);

        $this->_searchEngine = $this->getMockBuilder('\Magento\Search\Model\SearchEngine')
            ->setMethods(['search', 'getAggregations', 'getBucket', 'getValues'])
            ->disableOriginalConstructor()
            ->getMock();

        $searchEngineReflector = $reflectionOfCollection->getProperty('searchEngine');
        $searchEngineReflector->setAccessible(true);
        $searchEngineReflector->setValue($this->_collection, $this->_searchEngine);
    }

    /**
     * Test case for faced data of multiselect filters
     * @return void
     */
    public function testFacedDataForMultiSelectFilters()
    {
        $this->_requestBuilder->bind('category_ids', [1, 2, 3]);
        $this->_requestBuilder->bind('color', 'red');
        $this->_requestBuilder->bind('size', 'xs');
        $this->_requestBuilder->bind('search_term', 'the best product');
        $this->_requestBuilder->bind('activity', 120);

        $searchRequest = new \Magento\Framework\Search\Request(
            'catalog_view_container',
            'catalogsearch_fulltext',
            new BoolExpression('catalog_view_container', 1, [], [], []),
            0,
            10000,
            [
                new TermBucket('category_ids_bucket', 'category_ids', [1, 2, 3]),
                new TermBucket('color_bucket', 'color', ['red']),
                new TermBucket('size_bucket', 'size', ['xs']),
                new TermBucket('search_term_bucket', 'search_term', ['the best product']),
                new TermBucket('activity_bucket', 'activity', [120]),
            ],
            [
                'scope' => new Dimension('scope', 1)
            ]
        );

        $this->_requestBuilder->expects($this->any())
            ->method('create')
            ->willReturn($searchRequest);

        $this->_searchEngine->expects($this->any())
            ->method('search')
            ->willReturnSelf();

        $this->_searchEngine->expects($this->any())
            ->method('getAggregations')
            ->willReturnSelf();

        $this->_searchEngine->expects($this->any())
            ->method('getBucket')
            ->with('color_bucket')
            ->willReturnSelf();

        $this->_searchEngine->expects($this->any())
            ->method('getValues')
            ->willReturn([]);

        $this->_collection->getFacetedData('color');

        $expectedResult = [
            'category_ids' => [1, 2, 3],
            'search_term' => 'the best product',
            'color' => 'red',
            'size' => 'xs',
            'activity' => 120
        ];

        $actualResult = $this->_requestBuilder->getAllPlaceholders();
        $this->assertEquals($expectedResult, $actualResult);
    }
}
