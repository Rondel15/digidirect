<?php

namespace Digidirect\Collect\Helper\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Import
 *
 * @package Digidirect\Collect\Helper
 */
class Import extends AbstractHelper
{
    const XML_POST_CODE_PATH = 'carriers/collect/post_code_upload';

    /**
     * Import constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @param string $storeId
     * @param string $websiteId
     * @return DataObject
     */
    public function getScopeInfo($storeId, $websiteId)
    {
        $scopeId = 0;
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if (!empty($websiteId)) {
            $scopeType = ScopeInterface::SCOPE_WEBSITES;
            $scopeId = $websiteId;
        } elseif ($storeId !== '0') {
            $scopeType = ScopeInterface::SCOPE_STORES;
            $scopeId = $storeId;
        }

        return new DataObject(['scope_id' => $scopeId, 'scope_type' => $scopeType]);
    }

    /**
     * @param string $storeId
     * @param string $scope
     * @return string
     */
    public function getAuPostFile($storeId = null, $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(
            self::XML_POST_CODE_PATH,
            $scope,
            $storeId
        );
    }
}
