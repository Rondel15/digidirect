<?php
namespace Digidirect\SEO\Test\Unit\Plugin\Magento\Sitemap\Model\ResourceModel\Catalog;

use Digidirect\SEO\Plugin\Magento\Sitemap\Model\ResourceModel\Catalog\CategoryPlugin;
use Digidirect\SEO\Helper\Sitemap as SitemapHelper;
use Digidirect\SEO\Model\SitemapExcludeRepository;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class CategoryPluginTest
 * @package Digidirect\SEO\Test\Unit\Plugin\Magento\Sitemap\Model\ResourceModel\Catalog
 */
class CategoryPluginTest extends \PHPUnit_Framework_TestCase
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
     * @var CategoryPlugin
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
            CategoryPlugin::class,
            [
                'sitemapExcludeRepository' => $this->sitemapExcludeRepository,
                'sitemapHelper' => $this->sitemapHelper
            ]
        );
    }

    /**
     * @test
     */
    public function testAroundGetCollection()
    {
        $storeId = 1;
        $category = $this->getMock(\Magento\Sitemap\Model\ResourceModel\Catalog\Category::class, [], [], '', false);
        $this->sitemapHelper->expects($this->once())->method('isSitemapExcludeEnabled')->willReturn(true);
        $this->sitemapExcludeRepository->expects($this->once())->method('getExcludedItemIds')->willReturn([]);
        $origFuncMock = function ($storeId) {
            return [];
        };
        $this->assertTrue(is_array($this->plugin->aroundGetCollection($category, $origFuncMock, $storeId)));
    }
}
