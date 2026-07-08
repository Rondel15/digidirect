<?php
namespace Digidirect\AbstractAttributes\Helper;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Digidirect\AbstractAttributes\Api\OptionRepositoryInterface;
use Digidirect\AbstractAttributes\Api\Data\AbstractAttributeInterface;
use Digidirect\AbstractAttributes\Api\Data\OptionInterface;
use Digidirect\AbstractAttributes\Model\UrlProcessor\Option as OptionUrlProcessor;
use Digidirect\AbstractAttributes\Model\UrlProcessor\Attribute as AttributeUrlProcessor;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlFactory as MagentoUrlFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Url
 * @package Digidirect\AbstractAttributes\Helper
 */
class Url extends \Magento\Framework\App\Helper\AbstractHelper
{
    const URL_SUFFIX_CONFIG = 'digidirect_aa_config/general/url_suffix';

    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * @var OptionRepositoryInterface
     */
    protected $optionRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Url constructor.
     * @param Context $context
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     * @param OptionRepositoryInterface $optionRepositoryInterface
     * @param StoreManagerInterface $storeManager
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param StorageInterface $storage
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        AbstractAttributeRepositoryInterface $abstractAttributeRepository,
        OptionRepositoryInterface $optionRepositoryInterface,
        StoreManagerInterface $storeManager,
        UrlRewriteFactory $urlRewriteFactory,
        StorageInterface $storage,
        UrlFinderInterface $urlFinder,
        MagentoUrlFactory $urlFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        $this->optionRepository = $optionRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
        $this->urlFactory = $urlFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
    }

    /**
     * @param null|string|int $scopeCode
     * @return bool
     */
    public function getUrlSuffix($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::URL_SUFFIX_CONFIG,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }

    /**
     * Get attribute url
     * @param AbstractAttributeInterface $attribute
     * @param array $params
     * @return string
     */
    public function getAttributeUrl(AbstractAttributeInterface $attribute, $params = [])
    {
        $routePath = '';
        $requestPath = '';
        $routeParams = $params;

        $attrId = $attribute->getAttributeId();
        $storeId = $attribute->getStoreId();
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $filterData = [
            UrlRewrite::ENTITY_ID   => $attrId,
            UrlRewrite::ENTITY_TYPE => AttributeUrlProcessor::URL_ENTITY_TYPE,
            UrlRewrite::STORE_ID    => $storeId,
        ];

        $rewrite = $this->urlFinder->findOneByData($filterData);
        if ($rewrite) {
            $requestPath = $rewrite->getRequestPath();
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (empty($requestPath)) {
            $requestPath = sprintf(AttributeUrlProcessor::TARGET_PATH_PATTERN, $attrId);
        }

        if (isset($params['_request_path_only'])) {
            return $requestPath;
        }

        $routeParams['_direct'] = $requestPath;

        // Reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        return $this->getUrlInstance()->setScope($storeId)->getUrl($routePath, $routeParams);
    }

    /**
     * @param string $attributeCode
     * @param array $params
     * @return bool|string
     */
    public function getAttributeUrlByCode($attributeCode, $params = [])
    {
        $attributes = $this->abstractAttributeRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter(AbstractAttributeInterface::ATTRIBUTE_CODE, $attributeCode)
                ->setCurrentPage(1)
                ->setPageSize(1)
                ->create()
        );

        foreach ($attributes->getItems() as $attribute) {
            return $this->getAttributeUrl($attribute, $params);
        }

        return false;
    }

    /**
     * Get option url
     * @param OptionInterface $option
     * @param array $params
     * @return string
     */
    public function getOptionUrl(OptionInterface $option, $params = [])
    {
        $routePath = '';
        $requestPath = '';
        $routeParams = $params;

        $optionId = $option->getOptionId();
        $storeId = $option->getStoreId();
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $filterData = [
            UrlRewrite::ENTITY_ID   => $optionId,
            UrlRewrite::ENTITY_TYPE => OptionUrlProcessor::URL_ENTITY_TYPE,
            UrlRewrite::STORE_ID    => $storeId,
        ];

        $rewrite = $this->urlFinder->findOneByData($filterData);
        if ($rewrite) {
            $requestPath = $rewrite->getRequestPath();
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if (empty($requestPath)) {
            $aAttribute = $option->getAttribute();
            $attributeId = $aAttribute->getAttributeId();
            $attributeCode = $aAttribute->getAttributeCode();
            $requestPath = sprintf(OptionUrlProcessor::TARGET_PATH_PATTERN, $attributeCode, $attributeId, $optionId);
        }

        if (isset($routeParams['_request_path_only'])) {
            return $requestPath;
        }

        $routeParams['_direct'] = $requestPath;

        // Reset cached URL instance GET query params
        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        return $this->getUrlInstance()->setScope($storeId)->getUrl($routePath, $routeParams);
    }

    /**
     * @param int $optionId
     * @param array $params
     * @return bool|string
     */
    public function getOptionUrlById($optionId, $params = [])
    {
        $option = $this->optionRepository->getByOptionId($optionId);
        if ($option->getId()) {
            return $this->getOptionUrl($option, $params);
        }

        return false;
    }

    /**
     * Retrieve URL Instance
     * @return \Magento\Framework\UrlInterface
     */
    private function getUrlInstance()
    {
        return $this->urlFactory->create();
    }
}
