<?php

namespace Digidirect\Blog\Model;

use Digidirect\Blog\Helper\Data;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\UrlInterface;
use Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Digidirect\Blog\Model\ResourceModel\Category\Collection as CategoryCollection;

/**
 * Class UrlModel
 */
class UrlModel
{
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * UrlModel constructor.
     *
     * @param Data $dataHelper
     * @param UrlInterface $urlBuilder
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Data $dataHelper,
        UrlInterface $urlBuilder,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->dataHelper = $dataHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param AbstractModel $model
     * @param string $sourceField
     * @param string $urlKeyField
     * @return string
     */
    public function prepareUrlKey(AbstractModel $model, $sourceField, $urlKeyField = 'url_key')
    {
        $urlKey = '';
        if (!empty($model->getData($urlKeyField))) {
            $urlKey = $this->processUrlKey($model->getData($urlKeyField));
        } else {
            if (!empty($model->getData($sourceField))) {
                $urlKey = $this->processUrlKey($model->getData($sourceField));
            }
        }

        /**
         * @var $collection CategoryCollection
         */
        $collection = $this->categoryCollectionFactory->create();
        $collection->addFieldToFilter($urlKeyField, ['eq' => $urlKey])->setPageSize(1);
        $idToAdd = null;
        if ($model->getId()) {
            $collection->addFieldToFilter($model->getIdFieldName(), ['neq' => $model->getId()]);
            $idToAdd = $model->getId();
        }
        if ($collection->getFirstItem()->getId()) {
            if (!$idToAdd) {
                $idToAdd = (int)$model->getCollection()->getLastItem()->getId();
                $idToAdd++;
            }
            $urlKey = $urlKey . '-' . $idToAdd;
        }
        $model->setData($urlKeyField, $urlKey);
        return $urlKey;
    }

    /**
     * @param string $key
     * @return string
     */
    public function processUrlKey($key)
    {
        return preg_replace(
            '/^-+|-+$/',
            '',
            strtolower(
                preg_replace(
                    '/[^a-zA-Z0-9]+/',
                    '-',
                    $key
                )
            )
        );
    }

    /**
     * @param Post $post
     * @return string
     */
    public function getViewPostUrl(Post $post)
    {
        return $this->getUrl($this->getViewPostUrlPath($post));
    }

    /**
     * Get Post URL path without domain
     *
     * @param Post $post
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getViewPostUrlPath(Post $post)
    {
        $listUrl = $this->dataHelper->getGeneralSettingsConfig('list_url');

        return $listUrl . '/' . $post->getUrlKey();
    }

    /**
     * @param string $urlKey
     * @return string
     */
    public function getCategoryUrl($urlKey)
    {
        return $this->getUrl($this->getCategoryUrlPath($urlKey));
    }

    /**
     * Get Category URL path without domain
     *
     * @param string $urlKey
     * @return string
     */
    public function getCategoryUrlPath($urlKey)
    {
        $catPrefix = $this->dataHelper->getGeneralSettingsConfig('cat_prefix');
        $urlSuffixConfig = $this->dataHelper->getGeneralSettingsConfig('url_suffix');
        $urlSuffix = "";
        if (!empty($urlSuffixConfig)) {
            $urlSuffix = '.' . $urlSuffixConfig;
        }

        return $catPrefix . '/' . $urlKey . $urlSuffix;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getCategoryUrlKey($url)
    {
        $urlKey = '';
        if (!empty($url)) {
            $url = trim(str_replace($this->urlBuilder->getBaseUrl(), '', $url), '/');
            $parts = explode('/', $url);
            $categoryPrefix = $this->dataHelper->getGeneralSettingsConfig('cat_prefix');
            $categorySuffix = $this->dataHelper->getGeneralSettingsConfig('url_suffix');
            $urlKeyPart = false;

            if ($categoryPrefix) {
                if (!empty($parts) && $parts[0] == $categoryPrefix) {
                    if (!empty($urlSuffix)) {
                        $urlKeyPart = substr($parts[1], 0, -strlen($urlSuffix) - 1);
                    } else {
                        $urlKeyPart = $parts[1];
                    }
                }
            }
            $urlKey = str_replace('.' . $categorySuffix, '', $urlKeyPart);
        }
        return $urlKey;
    }

    /**
     * @param string $tagName
     * @return string
     */
    public function getTagUrl($tagName)
    {
        $urlSuffix = $this->dataHelper->getGeneralSettingsConfig('url_suffix');
        $listUrl = $this->dataHelper->getGeneralSettingsConfig('list_url');
        return $this->getUrl("$listUrl/tag/" . urlencode(strtolower($tagName)) . ($urlSuffix ? '.' . $urlSuffix : ''));
    }

    /**
     * @param bool $useBaseUrl
     * @return string
     */
    public function getBlogListUrl($useBaseUrl = true)
    {
        $listUrl = $this->dataHelper->getGeneralSettingsConfig('list_url');
        $prefix = $this->dataHelper->getGeneralSettingsConfig('url_prefix');
        $suffix = $this->dataHelper->getGeneralSettingsConfig('url_suffix');
        $url = !empty($prefix) ? $prefix . '/' . $listUrl : $listUrl;
        $url = !empty($suffix) ? $url . '.' . $suffix : $url;
        return $useBaseUrl === true ? $this->getUrl($url) : $url;
    }

    /**
     * Retrieve url
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    protected function getUrl($route, $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
