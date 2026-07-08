<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\AI\Helper;

use Digidirect\AI\Helper\Logger as LoggerHelper;
use Magento\Framework\DataObject;

/**
 * Class Engine
 *
 * @package Digidirect\AI\Helper
 */
class Engine extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * AJAX PROCESS STATUS HANDLE
     */
    const LOG_INFO_STOP_HANDLE = 'stop';

    /**
     * STOP AJAX PROCESS STATUS HANDLE
     */
    const LOG_INFO_PROCEED_HANDLE = 'start_update_process_status';

    /**
     * LoggerHelper
     *
     * @var LoggerHelper
     */
    protected $_logHelper;

    /**
     * Integrations
     *
     * @var \Digidirect\AI\Model\Integrations\Integrations
     */
    protected $_integrationFactory;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Transport Builder
     *
     * @var \Digidirect\AI\Model\Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Engine constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\AI\Model\Integrations\IntegrationsFactory $integration
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param LoggerHelper $logHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\AI\Model\Integrations\IntegrationsFactory $integration,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        LoggerHelper $logHelper
    ) {
        $this->_integrationFactory = $integration;
        $this->objectManager = $objectManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_logHelper = $logHelper;

        parent::__construct($context);
    }

    /**
     * Validate chain infinity
     *
     * @param string $baseProcessCode
     * @param string $chainProcessCode
     * @return DataObject
     */
    public function validateChainInfinity($baseProcessCode, $chainProcessCode)
    {
        $needToContinue = true;
        $infinity = false;

        $chainStack = [];
        $chainStack[] = $baseProcessCode;
        $chainStack[] = $chainProcessCode;

        if (trim($baseProcessCode) == trim($chainProcessCode)) {
            $infinity = true;
            $needToContinue = false;
        }

        $integration = $this->_integrationFactory->create();

        while ($needToContinue && ($chainProcessCode)) {
            $integration->unsetData();
            $integration->getResource()->load($integration, $chainProcessCode, 'process_code');

            if (trim($integration->getChildProcessCode())) {
                if (in_array($integration->getChildProcessCode(), $chainStack)) {
                    $needToContinue = false;
                    $infinity = true;
                }
            } else {
                $needToContinue = false;
            }

            $chainStack[] = $integration->getChildProcessCode();
            $chainProcessCode = $integration->getChildProcessCode();
        };

        $chStr = __('Infinity Call Stack : ');
        if ($infinity) {
            foreach ($chainStack as $one) {
                $chStr .= __('Process') . ' "' . $one . '" -> ';
            }
            $chStr .= ' ' . __('Loop');
        }

        $resp = new DataObject();
        $resp->setIsInfinity($infinity);
        $resp->setStack($chStr);

        return $resp;
    }
}
