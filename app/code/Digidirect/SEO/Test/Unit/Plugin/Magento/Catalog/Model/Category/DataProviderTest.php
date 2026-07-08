<?php
namespace Digidirect\SEO\Test\Unit\Plugin\Magento\Catalog\Model\Category;

use Digidirect\SEO\Helper\Sitemap as SitemapHelper;
use Digidirect\SEO\Model\SitemapExcludeRepository;
use Digidirect\SEO\Plugin\Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class DataProviderTest
 * @package Digidirect\SEO\Test\Unit\Plugin\Magento\Catalog\Model\Category
 */
class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapExcludeRepository
     */
    private $sitemapExcludeRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SitemapHelper
     */
    private $sitemapHelper;

    /**
     * @var DataProvider
     */
    private $plugin;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->sitemapExcludeRepository = $this->getMock(SitemapExcludeRepository::class, [], [], '', false);
        $this->sitemapHelper = $this->getMock(SitemapHelper::class, [], [], '', false);
        $objectManager = new ObjectManager($this);
        $this->plugin = $objectManager->getObject(
            DataProvider::class,
            [
                'sitemapExcludeRepository' => $this->sitemapExcludeRepository,
                'sitemapHelper' => $this->sitemapHelper
            ]
        );
    }

    /**
     * @test
     * @dataProvider isExcludeEnabledProvider
     */
    public function testAddSitemapExcludeField($isExcludeEnabled)
    {
        $category = $this->getMock(\Magento\Catalog\Model\Category\DataProvider::class, [], [], '', false);
        $this->sitemapHelper->expects($this->once())->method('isSitemapExcludeEnabled')->willReturn($isExcludeEnabled);
        $result = $this->plugin->afterPrepareMeta($category, ['search_engine_optimization' => ['children' => []]]);

        $this->assertEquals(
            $isExcludeEnabled,
            isset($result['search_engine_optimization']['children']['exclude_from_sitemap_fieldset'])
        );
    }

    /**
     * Provider for addExcludeFieldTest method
     *
     * @return array
     */
    public function isExcludeEnabledProvider()
    {
        return [[true], [false]];
    }

    /**
     * @test
     * @dataProvider isExcludedMapProvider
     */
    public function testSetFieldValue($isExcluded, $fieldValue)
    {
        $result = [2 => ['test_key' => 'test_value']];
        $this->sitemapHelper->expects($this->once())->method('isSitemapExcludeEnabled')->willReturn(true);
        $this->sitemapExcludeRepository->expects($this->any())->method('isExcluded')->willReturn($isExcluded);
        $category = $this->getMock(\Magento\Catalog\Model\Category\DataProvider::class, [], [], '', false);
        $output = $this->plugin->afterGetData($category, $result);
        foreach ($output as $categoryData) {
            $this->assertEquals($fieldValue, $categoryData['exclude_from_sitemap']);
        }
    }

    /**
     * Provider for addExcludeFieldTest method
     *
     * @return array
     */
    public function isExcludedMapProvider()
    {
        return [[true, true], [false, false]];
    }
}
