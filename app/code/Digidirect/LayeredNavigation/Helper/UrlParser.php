<?php
namespace Digidirect\LayeredNavigation\Helper;

use Digidirect\LayeredNavigation\Helper\Data;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Manager;

class UrlParser extends AbstractHelper
{
    const ALIAS_DELIMITER = ',';

    /**
     * @var \Digidirect\LayeredNavigation\Helper\Data
     */
    protected $seoHelper;

    /**
     * @var \Digidirect\LayeredNavigation\Helper\Url
     */
    protected $urlHelper;

    /**
     * UrlParser constructor.
     * @param Context $context
     * @param \Digidirect\LayeredNavigation\Helper\Data $seoHelper
     * @param \Digidirect\LayeredNavigation\Helper\Url $urlHelper
     */
    public function __construct(
        Context $context,
        Data $seoHelper,
        Url $urlHelper
    ) {
        parent::__construct($context);
        $this->seoHelper = $seoHelper;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param array $seoPart
     * @return array|false
     */
    public function parseSeoPart($seoPart)
    {
        $seoParts = explode('/', $seoPart);
        if (count($seoParts) % 2 !== 0) {
            return false;
        }

        $filters = [];
        for ($i = 0; $i < count($seoParts) - 1; $i += 2) {
            $attribute = $seoParts[$i];
            $aliases = explode(self::ALIAS_DELIMITER, $seoParts[$i + 1]);
            if (!isset($filters[$attribute])) {
                $filters[$attribute] = $aliases;
            } else {
                $filters[$attribute] = array_merge($filters[$attribute], $aliases);
            }
            $filters[$attribute] = array_unique($filters[$attribute]);
        }

        $params = $this->parseAliasesRecursively($filters);
        return $params;
    }

    /**
     * @param array $filters
     * @return array
     */
    protected function parseAliasesRecursively($filters)
    {
        $seoParams = [];
        $optionsData = $this->seoHelper->getOptionsSeoData();
        foreach ($filters as $attribute => $aliases) {
            $isAttributeFilter = false;
            foreach ($aliases as $alias) {
                foreach ($optionsData as $optionId => $option) {
                    if ($option['attribute_code'] == $attribute && $option['alias'] == $alias) {
                        $isAttributeFilter = true;
                        $seoParams = $this->addParsedOptionToParams($optionId, $attribute, $seoParams);
                    }
                }
            }

            if (!$isAttributeFilter) {
                $seoParams = $this->addParsedOptionToParams(
                    implode(self::ALIAS_DELIMITER, $aliases),
                    $attribute,
                    $seoParams
                );
            }
        }

        return $seoParams;
    }

    /**
     * @param string $value
     * @param string $paramName
     * @param array $params
     * @return mixed
     */
    protected function addParsedOptionToParams($value, $paramName, $params)
    {
        if (array_key_exists($paramName, $params)) {
            $params[$paramName] .= self::ALIAS_DELIMITER . $value;
        } else {
            $params[$paramName] = '' . $value;
        }

        return $params;
    }
}
