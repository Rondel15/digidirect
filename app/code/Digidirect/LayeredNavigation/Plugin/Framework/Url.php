<?php
namespace Digidirect\LayeredNavigation\Plugin\Framework;

use Digidirect\LayeredNavigation\Helper;
use Magento\Framework\UrlInterface;

class Url
{
    /**
     * @var Helper\Data
     */
    protected $helper;

    /**
     * @var Helper\Url
     */
    protected $urlHelper;

    /**
     * UrlPlugin constructor.
     * @param Helper\Data $helper
     * @param Helper\Url $urlHelper
     */
    public function __construct(
        Helper\Data $helper,
        Helper\Url $urlHelper
    ) {
        $this->helper = $helper;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param UrlInterface $subject
     * @param null|string $routePath
     * @param null|array $routeParams
     * @return array
     */
    public function beforeGetUrl(UrlInterface $subject, $routePath = null, $routeParams = null)
    {
        if (isset($routeParams['_clearFilters'])) {
            $routeParams['_query']['clearFilters'] = 1;
            unset($routeParams['_clearFilters']);
        } else if (isset($routeParams['_query']['clearFilters'])) {
            unset($routeParams['_query']['clearFilters']);
        }

        if ($this->helper->validateRequest()) {
            $routeParams['_escape'] = false;
        }

        return [$routePath, $routeParams];
    }

    /**
     * @param UrlInterface $subject
     * @param mixed $native
     * @return string
     */
    public function afterGetUrl(UrlInterface $subject, $native)
    {
        if ($this->urlHelper->isSeoUrlEnabled()) {
            $result = $this->urlHelper->seofyUrl($native);
            return $result;
        } else {
            return $native;
        }
    }
}
