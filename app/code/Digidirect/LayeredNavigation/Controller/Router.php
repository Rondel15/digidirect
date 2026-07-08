<?php
namespace Digidirect\LayeredNavigation\Controller;

use Digidirect\LayeredNavigation\Helper\Data;
use Digidirect\LayeredNavigation\Helper\FilterRemember;
use Digidirect\LayeredNavigation\Helper\Url;
use Digidirect\LayeredNavigation\Helper\UrlParser;
use Digidirect\LayeredNavigation\Helper\UrlBuilder;

class Router implements \Magento\Framework\App\RouterInterface
{
    const SEO_PART = 1;
    const SEO_MATCH = 2;

    /**
     * @var int
     */
    protected $_step;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var UrlParser
     */
    protected $urlParser;

    /**
     * Router constructor.
     * @param \Magento\Framework\Registry $registry
     * @param Data $helper
     * @param UrlParser $urlParser
     * @param Url $urlHelper
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        Data $helper,
        UrlParser $urlParser,
        Url $urlHelper
    ) {
        $this->registry = $registry;
        $this->helper = $helper;
        $this->urlHelper = $urlHelper;
        $this->urlParser = $urlParser;
    }

    /**
     * String
     * $params = array_merge($params, $request->getParams());
     * commented
     *
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (!$this->urlHelper->isSeoUrlEnabled() || $this->_step == self::SEO_MATCH) {
            return;
        }

        if ($this->_step == self::SEO_PART) {
            $this->_step = self::SEO_MATCH;
        }

        $path = trim($request->getPathInfo(), '/');
        if (!$path) {
            return;
        }

        if (($request->getControllerName() == 'noroute' && $request->getModuleName() == 'cms') === false
            && $this->_step == self::SEO_MATCH
        ) {
            return;
        }

        $matches = $this->helper->parseRequestUri($path);
        if ($matches === false) {
            return;
        }

        if ($this->_step != self::SEO_PART && $this->_step != self::SEO_MATCH) {
            $seoPart = $this->checkSeoPartInBasePath($path);
            if (!$seoPart) {
                $this->_step = self::SEO_PART;
                return;
            }
        }

        $seoPart = $this->urlHelper->removeCategorySuffix($matches[2]);
        $category = ($seoPart == $matches[2]) ? $matches[1] : $this->urlHelper->addCategorySuffix($matches[1]);

        $params = $this->urlParser->parseSeoPart($seoPart);
        if ($params === false) {
            $params = [];
        }

        $this->registry->register(UrlBuilder::SEO_PARSED_PARAMS, $params);
        $request->setParams($params);
        $request->setPathInfo($category);

        if ($this->_step == self::SEO_MATCH) {
            $request->setModuleName(null);
            $request->setControllerName(null);
            $request->setActionName(null);
        }

        $this->_step = self::SEO_MATCH;
    }

    /**
     * Check if we on standard url path without rewrite
     *
     * @param string $path
     * @return boolean
     */
    protected function checkSeoPartInBasePath($path)
    {
        $params = explode('/', $path);
        if (in_array(Url::FILTERS_DELIMITER, $params)) {
            return true;
        }
        return false;
    }
}
