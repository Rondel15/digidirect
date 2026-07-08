<?php
declare(strict_types=1);

namespace Digidirect\InfiniteScroll\Model;

use Digidirect\LayeredNavigation\Helper\Url;
use Magento\Eav\Model\Entity\TypeFactory;
use Digidirect\AbstractEntity\Model\AbstractEntityRepository;
use Digidirect\Digi\Setup\SeoBrandDescriptionEntitySetup;
use \Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\App\RequestInterface;
use \Magento\Framework\UrlInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Asset\GroupedCollection as PageAsset;
use Digidirect\Digi\Helper\AbstractAttribute;
use \Zend\Uri\Http as ZendUrlParser;
use \Digidirect\LayeredNavigation\Helper\UrlParser;

/**
 * Class SeoBrandDescription
 * @package Digidirect\Digi\Model
 */
class BrandProcessor
{
    const ASSET_CANONICAL = 'canonical';
    const EXCLUDED_ID_CATEGORIES = [1, 2];
    const BRAND_ATTR_CODE = 'brand';

    /**
     * @var AbstractEntityRepository
     */
    private $abstractEntityRepository;
    /**
     * @var Registry
     */
    private $coreRegistry;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var Config
     */
    private $pageConfig;
    /**
     * @var PageAsset
     */
    private $pageAsset;
    /**
     * @var AbstractAttribute
     */
    private $abstractAttributeHelper;
    /**
     * @var ZendUrlParser
     */
    private $zendUrlParser;

    /**
     * @var string
     */
    private $currentBrand;

    /**
     * @var UrlParser
     */
    private $urlParser;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * SeoBrandDescription constructor.
     * @param AbstractEntityRepository $abstractEntityRepository
     * @param Registry $coreRegistry
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param Config $pageConfig
     * @param PageAsset $pageAsset
     * @param AbstractAttribute $abstractAttributeHelper
     * @param ZendUrlParser $zendUrlParser
     * @param UrlParser $urlParser
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AbstractEntityRepository $abstractEntityRepository,
        Registry $coreRegistry,
        RequestInterface $request,
        UrlInterface $url,
        Config $pageConfig,
        PageAsset $pageAsset,
        AbstractAttribute $abstractAttributeHelper,
        ZendUrlParser $zendUrlParser,
        UrlParser $urlParser,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->abstractEntityRepository = $abstractEntityRepository;
        $this->coreRegistry = $coreRegistry;
        $this->request = $request;
        $this->url = $url;
        $this->pageConfig = $pageConfig;
        $this->pageAsset = $pageAsset;
        $this->abstractAttributeHelper = $abstractAttributeHelper;
        $this->zendUrlParser = $zendUrlParser;
        $this->urlParser = $urlParser;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return int|null
     */
    public function getCurrentOption()
    {
        if (!$this->currentBrand) {
            $this->currentBrand = 0;
            $optionId = null;
            
            $currentUrl = $this->getCurrentUrl();
            
            $urlKey = $currentUrl ? $this->parseUrl($currentUrl) : null;
            
            $brandId = $urlKey && !empty($urlKey) ? $this->abstractAttributeHelper->getBrandIdByUrlKey($urlKey) : null;
            if ($brandId) {
                $this->currentBrand = $brandId;
            }
        }
        
        return $this->currentBrand;
    }

    /**
     * @return mixed
     */
    public function getCurrentCategory(): ?CategoryInterface
    {
        $currentCategory = null;

        $result = $this->coreRegistry->registry('current_category');

        if ($result && $result->getId() && $this->checkCategory($result)) {
            $currentCategory = $result;
        }
        return $currentCategory;
    }

    /**
     * @param CategoryInterface $category
     * @return bool
     */
    public function checkCategory($category): bool
    {
        return !in_array($category->getId(), self::EXCLUDED_ID_CATEGORIES);
    }

    /**
     * @return null|string
     */
    private function getCurrentUrl()
    {
        $currentUrl = $this->url->getCurrentUrl();
        if (empty($currentUrl) || $this->isFiltered($currentUrl)) {
            $currentUrl = null;
        }
        return $currentUrl;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl()
    {
        $currentUrl = $this->getCurrentUrl();
        $urlKey = $currentUrl ? $this->parseUrl($currentUrl) : '';
        $currentCategory = $this->getCurrentCategory();

        $parentCategory = $currentCategory->getParentCategories();
        
        if (!empty($parentCategory) && count($parentCategory) > 1) {
            $currentCategory = array_shift($parentCategory);
        }
        return $currentCategory->getUrl() . '/' . $urlKey;
    }

    /**
     * @param string $url
     * @return mixed|string
     */
    private function parseUrl(string $url)
    {
        $lastPath = '';
        $urlPath = $this->zendUrlParser->parse($url)->getPath();
        if ($urlPath && !empty($urlPath)) {
            $pathArr = explode('/', $urlPath);
            $lastPath = array_pop($pathArr);
        }
        return $lastPath;
    }
    
    /**
     * @param null|string $currentUrl
     * @return bool
     */
    
    public function isFiltered($currentUrl = null)
    {
        $isFiltered = true;

        $currentUrl = $currentUrl ?: $this->url->getCurrentUrl();

        $path = preg_replace('/\?.*/i', '', $currentUrl);
        if (!preg_match('/^(.*)\/' . Url::FILTERS_DELIMITER . '\/(.*)$/', $path, $matches)) {
            $isFiltered = false;
        } else {
            if ($this->isOnlyBrandInFilter($matches[2])) {
                $isFiltered = false;
            }
        }

        return $isFiltered;
    }

    /**
     * @param string $seoPart
     * @return bool
     */
    private function isOnlyBrandInFilter($seoPart)
    {
        $params = explode('/', $seoPart);

        if (count($params) > 2
            || $params[0] != self::BRAND_ATTR_CODE
            || strpos($params[1], ',') !== false
        ) {
            return false;
        }

        return true;
    }
}