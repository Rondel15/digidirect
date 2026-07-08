<?php
declare(strict_types=1);

namespace ZV\SeoCompatible\Helper;

use Magento\Cms\Model\Page;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Page\Config as PageConfig;

class Canonical extends AbstractHelper
{
    /**
     * @var Page
     */
    protected $cmsPage;

    protected $logger;
    
    private  $pageConfig;
    
    protected $urlInterface;
    
    /**
     * Canonical constructor.
     * @param Context $context
     * @param Page    $cmsPage
     */
    public function __construct(
        Context $context,
        Page $cmsPage,
        Http $http,
        PageConfig $pageConfig,
        \Magento\Framework\UrlInterface $urlInterface,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->cmsPage = $cmsPage;
        $this->http = $http;
        $this->pageConfig = $pageConfig;
        $this->urlInterface = $urlInterface;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * This method is used in XML layout.
     * @return string
     */
    public function getCanonicalForAllCmsPages(): string
    {
        $checkModule = $this->http->getModuleName();
        //$this->logger->info('$checkModule: ' . $checkModule);
        
        if($this->scopeConfig->getValue('catalog/seo/cms_canonical_tag')){
            if ($this->cmsPage->getId()) {
                
                //$this->logger->info('$this->cmsPage->getIdentifier() ' . $this->cmsPage->getIdentifier());

                if ($this->cmsPage->getIdentifier() == "home") {
                    return $this->createLink(
                         rtrim($this->scopeConfig->getValue('web/secure/base_url'), '/')
                    );
                } elseif ($this->cmsPage->getIdentifier() == "find") {
                    $url = $this->urlInterface->getCurrentUrl();
                    //$this->logger->info('$url: ' . $url);
                    $urlComponents = parse_url($url);
                    
                    $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . $urlComponents['path'];
                
                    if (!empty($urlComponents['query'])) {
                        $this->pageConfig->setRobots("NOINDEX,NOFOLLOW");
                    }
                    //$this->logger->info('$canonical ' . $canonical);
                    return $this->createLink($canonical);
                    
                } else {
                    return $this->createLink(
                        $this->scopeConfig->getValue('web/secure/base_url') . $this->cmsPage->getIdentifier()
                    );
                }
            }
            if($checkModule == 'contact'){
                if ($this->cmsPage->getIdentifier() == "home") {
                    return $this->createLink(
                        rtrim($this->scopeConfig->getValue('web/secure/base_url'), '/')
                    );
                } else {
                    return $this->createLink(
                        $this->scopeConfig->getValue('web/secure/base_url') . $this->http->getModuleName()
                    );
                }
            } elseif ($checkModule == "customer") {
                $url = $this->urlInterface->getCurrentUrl();
                //$this->logger->info('$url: ' . $url);
                $urlComponents = parse_url($url);

                $canonical = $urlComponents['scheme'] . '://' . $urlComponents['host'] . '/customer/account/login';

                $this->pageConfig->setRobots("NOINDEX,NOFOLLOW");
                //$this->logger->info('$canonical ' . $canonical);
                return $this->createLink($canonical);

            }
        }
        return '';
    }

    /**
     * @param $url
     * @return string
     */
    protected function createLink($url): string
    {
        return '<link rel="canonical" href="' . $url . '" />';

    }
}