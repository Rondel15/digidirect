<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\Catalog\Block\Category;

use \Magento\Framework\UrlInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Class View
 * @api
 * @package Magento\Catalog\Block\Category
 * @since 100.0.2
 */
class View extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var UrlInterface
     */
    private $url;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_categoryHelper;
    
    protected $logger;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        UrlInterface $url,
        CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->_categoryHelper = $categoryHelper;
        $this->_catalogLayer = $layerResolver->get();
        $this->_coreRegistry = $registry;
        $this->url = $url;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getLayout()->createBlock(\Magento\Catalog\Block\Breadcrumbs::class);

        $category = $this->getCurrentCategory();
        if ($category) {
            $title = $category->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = $category->getMetaDescription();
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $category->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            if ($this->_categoryHelper->canUseCanonicalTag()) {
                
                $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
                $this->logger->info('Current URL: ' . $currentUrl);

                $urlComponents = parse_url($currentUrl);

                $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'];
                
                if (str_contains($currentUrl, 'catalog/category/view')) {
                    $canonical = $category->getUrl();
                    $this->logger->info('catalog/category/view: ' . $category->getUrl());
                }

                if (!empty($urlComponents['query'])) {

                    $this->pageConfig->setRobots("NOINDEX,NOFOLLOW");
                    
                    $params = [];
                    
                    parse_str($urlComponents['query'], $params);

                    if (!empty($params['p']) || !empty($params['page'])) {

                        if (count($params) == 1) {
                            $this->pageConfig->setRobots("INDEX,FOLLOW");
                        }

                        if ($params['p'] == 1) {
                            $page = ''; 
                        } else {
                            $page = '?p=' . $params['p']; 
                        }

                        if ($params['page'] == 1) {
                            $page = ''; 
                        } else {
                            $page = '?page=' . $params['page']; 
                        }

                        $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'] . $page;
                      
                    } else {
                        $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'];
                    }
                }
            }

            //$this->logger->info('$canonical ' . $canonical);

            $this->pageConfig->addRemotePageAsset(
                $canonical,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->getCurrentCategory()->getName());
            }
            
        } else { //Fix canonical for non seo friendly category pages
            
            $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
            $this->logger->info('Current URL: ' . $currentUrl);
            
            if (str_contains($currentUrl, 'catalog/category/view')) {
                //$this->logger->info('catalog/category/view: ' . $url);

                $parts = explode('/', trim($currentUrl, '/'));

                $id = null;
                for ($i = 0; $i < count($parts); $i++) {
                    if ($parts[$i] === 'id' && isset($parts[$i + 1])) {
                        $id = $parts[$i + 1];
                        break;
                    }
                }

                try {
                    $category = $this->categoryRepository->get($id);
                    $this->logger->info('catalog/category/view: ' . $category->getUrl());

                    if ($this->request->getFullActionName() === 'catalog_category_view') {
                        $this->pageConfig->addRemotePageAsset(
                            $currentUrl,
                            'canonical',
                            ['attributes' => ['rel' => 'canonical']]
                        );
                    }

                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    return null;
                }
                //$canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'];
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }

    /**
     * Retrieve current category model object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', $this->_coreRegistry->registry('current_category'));
        }
        return $this->getData('current_category');
    }

    /**
     * @return mixed
     */
    public function getCmsBlockHtml()
    {
        if (!$this->getData('cms_block_html')) {
            $html = $this->getLayout()->createBlock(
                \Magento\Cms\Block\Block::class
            )->setBlockId(
                $this->getCurrentCategory()->getLandingPage()
            )->toHtml();
            $this->setData('cms_block_html', $html);
        }
        return $this->getData('cms_block_html');
    }

    /**
     * Check if category display mode is "Products Only"
     * @return bool
     */
    public function isProductMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PRODUCT;
    }

    /**
     * Check if category display mode is "Static Block and Products"
     * @return bool
     */
    public function isMixedMode()
    {
        return $this->getCurrentCategory()->getDisplayMode() == \Magento\Catalog\Model\Category::DM_MIXED;
    }

    /**
     * Check if category display mode is "Static Block Only"
     * For anchor category with applied filter Static Block Only mode not allowed
     *
     * @return bool
     */
    public function isContentMode()
    {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode() == \Magento\Catalog\Model\Category::DM_PAGE) {
            $res = true;
            if ($category->getIsAnchor()) {
                $state = $this->_catalogLayer->getState();
                if ($state && $state->getFilters()) {
                    $res = false;
                }
            }
        }
        return $res;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->getCurrentCategory()->getIdentities();
    }
}
