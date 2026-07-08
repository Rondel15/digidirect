<?php

namespace Digidirect\AbstractGiftCard\Service\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Digidirect\AbstractGiftCard\Service\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    const DEFAULT_PATH_PATTERN = 'giftcard_service/%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var string|null
     */
    protected $_serviceCode;

    /**
     * @var string|null
     */
    protected $_pathPattern;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $serviceCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $serviceCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_serviceCode = $serviceCode;
        $this->_pathPattern = $pathPattern;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setServiceCode($serviceCode)
    {
        $this->_serviceCode = $serviceCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->_pathPattern = $pathPattern;
    }

    /**
     * Retrieve information from service configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        if ($this->_serviceCode === null || $this->_pathPattern === null) {
            return null;
        }
        
        return $this->_scopeConfig->getValue(
            sprintf($this->_pathPattern, $this->_serviceCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
