<?php
namespace Digidirect\Digi\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\GroupedCollection as PageAsset;
use Magento\Framework\View\Page\Config;

/**
 * Class Canonical
 * @package Digidirect\Digi\Helper
 */
class Canonical extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ASSET_CANONICAL = 'canonical';

    const CONFIG_MAPPER_PATH = 'custom_canonicals/general/mapper';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Config
     */
    protected $pageConfig;

    /**
     * @var PageAsset
     */
    protected $pageAsset;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Canonical constructor.
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param Config $pageConfig
     * @param PageAsset $pageAsset
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $url,
        Config $pageConfig,
        PageAsset $pageAsset,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->url = $url;
        $this->pageConfig = $pageConfig;
        $this->pageAsset = $pageAsset;
        $this->scopeConfig = $scopeConfig;
    }

    public function setCanonicalAlias()
    {
        $url = preg_replace('/\?.*/i', '', $this->url->getCurrentUrl());
        $toFind = $this->prepareLink($url);

        if (!$toFind) return;
        $canonicalUrl = $this->getCanonicalMap($toFind);

        if ($canonicalUrl) {
           $this->setCanonicalUrl($this->url->getBaseUrl() . $canonicalUrl);
        }
    }

    /**
     * @param string $search
     * @return string
     */
    private function getCanonicalMap(string $search):string
    {
        $links = json_decode($this->scopeConfig->getValue(self::CONFIG_MAPPER_PATH), true);
        if (!empty($links)) {
            foreach ($links as $link) {
                $request = $this->prepareLink($link['request_path']);
                if ($search == $request) {
                    return $this->prepareLink($link['canonical_link']);
                }
            }
        }

        return '';
    }

    /**
     * @param string $link
     * @return string
     */
    private function prepareLink(string $link):string
    {
        $baseUrl = $this->url->getBaseUrl();
        return trim(str_replace($baseUrl, '', $link), '/');
    }

    /**
     * @param string $url
     * @return bool
     */
    private function setCanonicalUrl(string $url)
    {
        if (empty($url)) return false;

        foreach ($this->pageAsset->getAll() as $urlKey => $asset) {
            if ($asset->getContentType() == self::ASSET_CANONICAL) {
                $this->pageAsset->remove($urlKey);
            }
        }

        $this->pageConfig->addRemotePageAsset(
            'canonical-canonical',
            self::ASSET_CANONICAL,
            ['attributes' => ['rel' => self::ASSET_CANONICAL]]
        );

        return true;
    }
}
