<?php

namespace Digidirect\AI\Helper;

use Symfony\Component\Config\Definition\Exception\Exception;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Digidirect\AI\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CRON_REGEXP = '/^((?:[1-9]?\d|\*)\s*(?:(?:[\/-][1-9]?\d)|(?:,[1-9]?\d)+)?\s*){5}$/';

    const PATH_INTEGRATIONS = 'digidirect_integrations';

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }
}
