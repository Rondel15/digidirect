<?php
declare(strict_types=1);

namespace Digidirect\Digi\Model;

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
class SeoBrandDescription
{
    const ASSET_CANONICAL = 'canonical';
    const EXCLUDED_ID_CATEGORIES = [1, 2];
    const ROBOTS_META_DATA = 'INDEX,FOLLOW';
    const BRAND_ATTR_CODE = 'brand';
    const META_TITLE_MASK = 'Buy {{brand}} Products Online';

    /**
     * @var bool
     */
    private $isSeoBrnadUse = false;
    /**
     * @var null
     */
    private $seoBrandEntity = null;

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
     * @return bool
     */
    public function isSeoBrandDescriptionUse()
    {
        return $this->isSeoBrnadUse;
    }

    /**
     * @param $seoBrandEntity
     * @return void
     */
    public function setSeoBrandEntity($seoBrandEntity)
    {
        if ($seoBrandEntity && $seoBrandEntity->getId()) {
            $this->seoBrandEntity = $seoBrandEntity;
            $this->isSeoBrnadUse = true;
        }
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
     * @return null
     */
    public function getSeoBrandEntity()
    {
        $currentCategory = $this->getCurrentCategory();
        if (!$this->seoBrandEntity && $currentCategory) {
            $currentOption = $this->getCurrentOption();
            if ($currentOption && $currentCategory) {
                $entityType = $this->abstractEntityRepository
                    ->getCollection(SeoBrandDescriptionEntitySetup::ABSTRACT_ENTITY_NAME)
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('brand', ['eq' => $currentOption])
                    ->addAttributeToFilter('catogory', ['eq' => $currentCategory->getId()])
                    ->setPage(1, 1);
                $this->setSeoBrandEntity($entityType->getFirstItem());
            }
        }

        return $this->seoBrandEntity;
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
     * @param string $robotsMetaData
     * @return void
     */
    public function setDefaultMetaInformation($robotsMetaData = self::ROBOTS_META_DATA)
    {
        if ($this->isCategoryBrandPage()) {
            //$this->pageConfig->setRobots($robotsMetaData);
            $this->setCanonical($this->getCanonicalUrl());

            //clint comment out Nov 9, 2023
//            if (!$this->seoBrandEntity || !$this->seoBrandEntity->getMetaTitle()) {
//                $brandName = $this->getBrandLabel($this->getCurrentOption());
//                $this->setDefaultMetaTitle($brandName);
//            }
        } else {
            if ($this->isFiltered()) {
                $this->setMetaTitle(
                    $this->getTitleFromFilterLabel($this->getLastFilterLabel())
                    . ': ' . $this->pageConfig->getTitle()->getShortHeading()
                );
            }
        }
    }

    /**
     * @return array
     */
    public function getLastFilterLabel()
    {
        $url = preg_replace('/\?.*/i', '', $this->url->getCurrentUrl());
        $categorySuffix = $this->scopeConfig->getValue('catalog/seo/category_url_suffix');
        if(!empty($categorySuffix))
        {
            $seoPart = trim(str_replace($categorySuffix, '', last(explode(Url::FILTERS_DELIMITER, $url))), '/');
        }
        else
        {
            $seoPart = "";
        }
        $params = $this->urlParser->parseSeoPart($seoPart);

        if (!empty($params)) {
            $labels = [];
            $lastParam = explode(',', last($params));
            foreach ($lastParam as $option) {
                $labels[] = $this->abstractAttributeHelper->getOptionLabel($option);
            }
            return $labels;
        }

        return ['Filter'];
    }

    /**
     * @param array $filterNames
     * @return string
     */
    public function getTitleFromFilterLabel(array $filterNames)
    {
        if (!empty($filterNames)) {
            return implode(',', $filterNames);
        }
        return 'Filter';
    }

    /**
     * @param string $rowId
     * @return string
     */
    public function getBrandLabel($rowId)
    {
        return $this->abstractAttributeHelper->getBrandLabel($rowId);
    }

    /**
     * @param SeoBrandDescription $seoBrandEntity
     * @return void
     */
    public function setMetaInformationByEntity($seoBrandEntity)
    {
        if ($this->pageConfig) {
            if ($seoBrandEntity->getMetaTitle()) {
                $this->setMetaTitle($seoBrandEntity->getMetaTitle());
            }

            if ($seoBrandEntity->getMetaDescription()) {
                $this->pageConfig->setDescription($seoBrandEntity->getMetaDescription());
            }

            if ($seoBrandEntity->getMetaKeywords()) {
                $this->pageConfig->setKeywords($seoBrandEntity->getMetaKeywords());
            }
        }
    }

    /**
     * @param string $brandName
     * @return void
     */
    public function setDefaultMetaTitle($brandName)
    {
        $metaTitle = str_replace('{{'. self::BRAND_ATTR_CODE .'}}', $brandName, self::META_TITLE_MASK);
        $this->setMetaTitle($metaTitle);
    }

    /**
     * @param string $title
     * @return void
     */
    public function setMetaTitle($title)
    {
        $titleObj = $this->pageConfig->getTitle();
        $titleObj->set($title);
        $metaTitleWithConfig = $titleObj->get();
        $this->pageConfig->setMetaTitle($metaTitleWithConfig);
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

    /**
     * @param string $url
     * @return void
     */
    private function setCanonical($url)
    {
        if ($url && !empty($url)) {
            foreach ($this->pageAsset->getAll() as $urlKey => $asset) {
                if ($asset->getContentType() == self::ASSET_CANONICAL) {
                    $this->pageAsset->remove($urlKey);
                }
            }
            $this->pageConfig->addRemotePageAsset(
                'canonical-seo',
                self::ASSET_CANONICAL,
                ['attributes' => ['rel' => self::ASSET_CANONICAL]]
            );
        }
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
     * @return bool
     */
    public function isCategoryBrandPage()
    {
        return $this->getCurrentOption() && $this->getCurrentCategory();
    }

    /**
     * @return bool
     */
    public function isFilterPage()
    {
        $currentUrl = $this->url->getCurrentUrl();
        if (empty($currentUrl) || strpos($currentUrl, '/filters/') !== false) {
            return true;
        }
        return false;
    }
}
