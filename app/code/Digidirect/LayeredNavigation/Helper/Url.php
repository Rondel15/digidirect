<?php
namespace Digidirect\LayeredNavigation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager;

class Url extends AbstractHelper
{
    const FILTERS_DELIMITER = 'filters';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * Url constructor.
     * @param Context $context
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Data $helper
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->moduleManager = $context->getModuleManager();
    }

    /**
     * Make seo url, overwritten function from extension
     * @param string $url
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function seofyUrl($url)
    {
        if (!preg_match('@^([^/]*//[^/]*/)(.*)$@', $url, $globalParts)) {
            return $url;
        }

        $delimiter = strpos($url, '&amp;') === false ? '&' : '&amp;';
        $nativeParts = explode('?', $globalParts[2], 2);
        if ($this->_request->getModuleName() == 'catalogsearch') {
            $routeUrl = $this->getCatalogSearchRoutePart($nativeParts[0]);
        } else {
            $routeUrl = $this->removeCategorySuffix($nativeParts[0]);
        }

        $appendSuffix = $routeUrl != $nativeParts[0];
        $endsWithLine = strlen($routeUrl) && $routeUrl[strlen($routeUrl) - 1] == '/';
        if ($endsWithLine) {
            if (!$this->needSeofyUrlForModule()) {
                return $url;
            }
            $routeUrl = substr($routeUrl, 0, -1);
        }

        $resultPath = $routeUrl;
        $query = [];
        $hashPart = '';
        if (isset($nativeParts[1])) {
            $paramPart = $nativeParts[1];
            $hashPosition = strpos($paramPart, '#');
            if ($hashPosition !== false) {
                $hashPart = substr($paramPart, $hashPosition);
                $paramPart = substr($paramPart, 0, $hashPosition);
            }

            if (strlen($paramPart)) {
                $query = explode($delimiter, $paramPart);
                $seoAliases = $this->query2Aliases($query);
                if ($seoAliases) {
                    $resultPath = $this->injectAliases($resultPath, $seoAliases);
                }
            }
        }

        $resultPath = ltrim($resultPath, '/');
        if ($appendSuffix) {
            $resultPath = $this->addCategorySuffix($resultPath);
        } else if ($endsWithLine && empty($query)) {
            $resultPath .= '/';
        }

        $result = $query ? ($resultPath . '?' . implode($delimiter, $query)) : $resultPath;
        $result .= $hashPart;

        return $globalParts[1] . $result;
    }

    /**
     * Check if current module need use custom rewrites
     * @return bool
     */
    public function needSeofyUrlForModule()
    {
        $additionalModulesForSeoUrlArray = [
            'catalogsearch' => [
                'action' => 'index',
                'controller' => 'result'
            ]
        ];

        $request = $this->_request;
        $arrayAction = [
            'action' => $request->getActionName(),
            'controller' => $request->getControllerName()
        ];

        $array = isset($additionalModulesForSeoUrlArray[$this->_request->getModuleName()])
            ? $additionalModulesForSeoUrlArray[$this->_request->getModuleName()]
            : [];

        $diff = array_diff_assoc(
            $arrayAction,
            $array
        );

        return empty($diff);
    }

    /**
     * @param array $query
     * @return array
     */
    protected function query2Aliases(array &$query)
    {
        $optionsData = $this->helper->getOptionsSeoData();
        $seoAliases = [];
        foreach ($query as $key => $queryArgument) {
            $argumentParts = explode('=', $queryArgument, 2);
            if (count($argumentParts) == 2) {
                $paramName = $argumentParts[0];
                $values = explode(
                    UrlParser::ALIAS_DELIMITER,
                    str_replace('%2C', UrlParser::ALIAS_DELIMITER, $argumentParts[1])
                );

                foreach ($values as $value) {
                    if ($this->isParamSeoSignificant($paramName)) {
                        $alias = $value;
                        if (array_key_exists($value, $optionsData)) {
                            $alias = $optionsData[$value]['alias'];
                        }

                        $seoAliases[$paramName][] = $alias;
                        unset($query[$key]);
                    }
                }
            }
        }

        return $seoAliases;
    }

    /**
     * @param string $param
     * @return mixed
     */
    public function isParamSeoSignificant($param)
    {
        $seoData = $this->helper->getOptionsSeoData();
        foreach ($seoData as $seoAttribute) {
            if ($seoAttribute['attribute_code'] == $param) {
                return true;
            }
        }

        if ($param == Data::CATEGORY_REQUEST_VAR) {
            return true;
        }

        return false;
    }

    /**
     * @param string $routeUrl
     * @param array $aliases
     * @return string
     */
    protected function injectAliases($routeUrl, array $aliases)
    {
        $result = $routeUrl;
        if (!empty($aliases)) {
            $result .= '/' . self::FILTERS_DELIMITER . '/';
            foreach ($aliases as $attribute => $values) {
                $result .= $attribute . '/' . implode(UrlParser::ALIAS_DELIMITER, $values) . '/';
            }
            $result = rtrim($result, '/');
        }

        return $result;
    }

    /**
     * @param string $url
     * @return string
     */
    public function addCategorySuffix($url)
    {
        if (strpos($url, 'catalogsearch') !== false) {
            return $url;
        }

        $suffix = $this->helper->getSuffix();
        if (strlen($suffix)) {
            $url .= $suffix;
        }

        return $url;
    }

    /**
     * @param string $url
     * @return mixed
     */
    public function removeCategorySuffix($url)
    {
        $suffix = $this->helper->getSuffix();
        if(!empty($suffix))
        {
            if (strlen($suffix)) {
                $p = strrpos($url, $suffix);
                if ($p !== false && $p == strlen($url) - strlen($suffix)) {
                    $url = substr($url, 0, $p);
                }
            }
        }
        
        return $url;
    }

    /**
     * @param string $url
     * @return mixed
     */
    public function getCatalogSearchRoutePart($url)
    {
        $catalogSearchRoutes = ['catalogsearch/result/index', 'catalogsearch/result'];
        foreach ($catalogSearchRoutes as $searchPart) {
            $p = strpos($url, $searchPart);
            if ($p !== false) {
                return substr($url, 0, $p) . $searchPart;
            }
        }
        return $url;
    }

    /**
     * @return bool
     */
    public function isSeoUrlEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'digidirect_layerednavigation/url/mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
}
