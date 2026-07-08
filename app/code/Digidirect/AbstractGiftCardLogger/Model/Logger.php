<?php

namespace Digidirect\AbstractGiftCardLogger\Model;

use Digidirect\AI\Model\Logger\Logger as AILogger;

class Logger extends \Digidirect\AI\Model\Logger\Logger
{
    /**
     * @var string
     */
    protected $_commandCode;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Logger constructor.
     * @param \Digidirect\AI\Helper\Logger $logHelper
     * @param \Digidirect\AI\Model\Logger\Types\DbFactory $dbLoggerFactory
     * @param \Digidirect\AI\Model\Logger\Types\FileFactory $fileLoggerFactory
     * @param \PSR\Log\LoggerInterface $psrLogger
     * @param \Digidirect\AI\Model\Integrations\IntegrationsFactory $integrationsFactory
     * @param \Digidirect\AI\Model\Logger\Types\EmailFactory $emailLoggerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $integration
     * @param array $alternativeLoggers
     * @param string $commandCode
     */
    public function __construct(
        \Digidirect\AI\Helper\Logger $logHelper,
        \Digidirect\AI\Model\Logger\Types\DbFactory $dbLoggerFactory,
        \Digidirect\AI\Model\Logger\Types\FileFactory $fileLoggerFactory,
        \PSR\Log\LoggerInterface $psrLogger,
        \Digidirect\AI\Model\Integrations\IntegrationsFactory $integrationsFactory,
        \Digidirect\AI\Model\Logger\Types\EmailFactory $emailLoggerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $integration = null,
        $alternativeLoggers = [],
        $commandCode = null
    ) {
        if (!$integration instanceof \Digidirect\AI\Model\Integrations\Integrations) {
            $integration = $integrationsFactory->create([
                'data' => [
                    'integration_name'  => 'AbstractGiftCard',
                    'process_code'      => 'AbstractGiftCard'
                ]
            ]);
        }

        $alternativeLoggers[self::LOG_PLACE_EMAIL] = $emailLoggerFactory->create([
            'data' => ['logger' => $this],
            'subject' => __('AbstractGiftCard Integration Log'),
        ]);

        $this->_commandCode = $commandCode;
        $this->storeManager = $storeManager;
        parent::__construct(
            $logHelper,
            $dbLoggerFactory,
            $fileLoggerFactory,
            $psrLogger,
            $integration,
            $alternativeLoggers
        );
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @param string $place
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return Logger
     */
    public function log($level, $message, array $context = [], $place = AILogger::LOG_PLACE_FILE_AND_DB)
    {
        if (!$this->getDbLogger()->getId()) {
            $this->addHeader('Initiator: AbstractGiftCard. ' . $this->getCommandCode());
            $website = $this->storeManager->getWebsite();
            if ($website->getId()) {
                $this->addIdentifyingParams(['website' => $website->getCode()], $place);
            }
        }

        if (in_array($level, [\Monolog\Logger::ERROR, \Monolog\Logger::CRITICAL])) {
            $place = AILogger::LOG_PLACE_EVERYWHERE;
        }

        return parent::log($level, $message, $context, $place);
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCommandCode($code)
    {
        $this->_commandCode = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommandCode()
    {
        return $this->_commandCode;
    }
}
