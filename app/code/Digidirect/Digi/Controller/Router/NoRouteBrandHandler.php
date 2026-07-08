<?php

namespace Digidirect\Digi\Controller\Router;

use \Magento\Framework\App\RequestInterface;
use \Digidirect\LayeredNavigation\Helper\Url;

class NoRouteBrandHandler implements \Magento\Framework\App\Router\NoRouteHandlerInterface
{
    const BRAND_FILTER = 'brand';

    protected $_isProcessed = false;

    /**
     * @var \Digidirect\Digi\Helper\AbstractAttribute
     */
    protected $aaHelper;

    /**
     * NoRouteBrandHandler constructor.
     * @param \Digidirect\Digi\Helper\AbstractAttribute $aaHelper
     */
    public function __construct(
        \Digidirect\Digi\Helper\AbstractAttribute $aaHelper
    ) {
        $this->aaHelper = $aaHelper;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function process(RequestInterface $request)
    {
        if ($this->_isProcessed) return false;

        /**
         * @var \Magento\Framework\App\Request\Http $request
         */
        $path = $request->getRequestUri();

        $withoutGet = explode('?', $path);

        $parts = explode('/', $withoutGet[0]);

        $possibleBrand = explode('.', array_pop($parts));

        $optionId = $this->getBrandOption($possibleBrand[0]);

        if ($optionId) {
            $request->setParams([self::BRAND_FILTER => $optionId]);
            $request->setPathInfo(implode('/', $parts));
            $this->_isProcessed = true;
            return true;
        }

        return false;
    }

    /**
     * @param string $brand
     * @return int|null
     */
    protected function getBrandOption(string $brand)
    {
        if (!empty($brand)) {
            return $this->aaHelper->getBrandOptionIdByUrlKey($brand);
        }

        return null;
    }
}
