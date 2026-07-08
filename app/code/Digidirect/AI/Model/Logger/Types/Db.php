<?php

namespace Digidirect\AI\Model\Logger\Types;

use Digidirect\AI\Model\Engine\Processor\Exception\ProcessException;
use Digidirect\AI\Model\Logger\Exception\LoggerException;

/**
 * Class Logger
 *
 * @method \Digidirect\AI\Model\ResourceModel\Logger\Logger getResource()
 * @package Digidirect\AI\Model\Logger
 */
class Db extends \Magento\Framework\Model\AbstractModel implements TypesInterface
{
    /**
     * @var \Digidirect\AI\Model\Logger\Logger
     */
    protected $_logger;

    /**
     * @var bool
     */
    protected $hasDetails = false;

    /**
     * @inheritdoc
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AI\Model\ResourceModel\Logger\Logger');
    }

    /**
     * @param string $resourceModel
     * @return void
     */
    protected function _init($resourceModel)
    {
        $this->_logger = $this->getData('logger');
        parent::_init($resourceModel);
    }

    /**
     * @return \Digidirect\AI\Model\Logger\Logger
     */
    protected function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @return \Digidirect\AI\Model\Integrations\Integrations|null
     */
    protected function getIntegration()
    {
        return $this->getLogger()->getIntegration();
    }

    /**
     * @return \Digidirect\AI\Helper\Logger
     */
    protected function getHelper()
    {
        return $this->getLogger()->getHelper();
    }

    /**
     * @return mixed
     */
    public function getRecordIdentifier()
    {
        return $this->getId();
    }

    /**
     * @return $this
     */
    public function saveUsingDirectQuery()
    {
        /* Comes to constructor in $data parameter  */
        $logger = $this->getLogger();
        if (!$logger instanceof \Digidirect\AI\Model\Logger\LoggerInterface || !$logger->getMessageLevel()) {
            return $this;
        }

        $integration = $logger->getIntegration();
        if (!$integration instanceof \Digidirect\AI\Model\Integrations\Integrations) {
            return $this;
        }

        $logId = $this->getId();
        if (!$logId) {
            if (!$integration->getProcessCode() || !$integration->getIntegrationName()) {
                return $this;
            }
        }

        $logFile = $this->getData('log_file');
        if (!$logFile) {
            $logFile = $logger->getLogRecordIdentifier(\Digidirect\AI\Model\Logger\Logger::LOG_PLACE_FILE);
        }
        $now = date('Y-m-d H:i:s');
        $data = [
            'updated_at' => $now,
            'type' => $logger->getMessageLevel(),
            'comment' => $this->getData('comment') ?: '',
            'identifying_params' => $this->getData('identifying_params') ?: '',
            'log_file' => $logFile,
        ];

        if ($logId) {
            foreach ($data as $k => $v) {
                if ($this->getOrigData($k) == $v) {
                    unset($data[$k]);
                }
            }
            if ($data) {
                $this->addData($data);
                $this->getResource()->updateLogData($logId, $data);
            }
        } else {
            $data['created_at'] = $now;
            $data['sync_type'] = $integration->getIntegrationName() . $this->getChainStack();
            $data['process_code'] = $integration->getProcessCode();
            $this->addData($data);
            $logId = $this->getResource()->saveLogData($data);
            $this->setId($logId);
        }

        $this->setOrigData();

        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    protected function appendDetails($message)
    {
        if (!$this->hasDetails()) {
            $this->setData('details', $message);
            $this->getResource()->setDetails($this, $message);
            $this->hasDetails = true;
        } else {
            $this->setData('details', $this->getData('details') . PHP_EOL . $message);
            $this->getResource()->addDetails($this, $message);
        }
        return $this;
    }

    /**
     * @param string $message
     * @param string $level
     * @return $this
     * @throws \Exception
     */
    public function addHeader($message, $level = null)
    {
        if(!empty($this->getComment()))
        {
            $comment = trim($this->getComment());
        }
        else
        {
            $comment = $this->getComment();
        }
        $comment = $comment ? $comment . PHP_EOL . $message : $message;
        $this->setComment($comment);
        $this->saveUsingDirectQuery();

        $level = $level ? $level : \Digidirect\AI\Helper\Logger::RECORD_TYPE_INFO_CODE;
        $this->appendDetails($this->getHelper()->_getLevelWrapper($level) . $message);
        return $this;
    }

    /**
     * @param string|array $identifiers
     * @return $this
     * @throws \Exception
     */
    public function addIdentifyingParams($identifiers)
    {
        if (is_array($identifiers)) {
            $parts = [];
            $keys = array_keys($identifiers);
            if (array_keys($keys) !== $keys) {
                foreach ($identifiers as $key => $value) {
                    $parts[] = sprintf('%s: %s', $key, $value);
                }
            } else {
                $parts = $identifiers;
            }

            $identifiers = implode(PHP_EOL, $parts);
        }

        if ($identifiers = trim($identifiers)) {
            $prevIdentifiers = $this->getData('identifying_params');
            $identifiers = $prevIdentifiers ? $prevIdentifiers . PHP_EOL . $identifiers : $identifiers;
            $this->setData('identifying_params', $identifiers);
            $this->saveUsingDirectQuery();
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function hasDetails()
    {
        if ($this->hasDetails === null) {
            $this->hasDetails = $this->getResource()->hasDetails($this);
        }
        return $this->hasDetails;
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        $this->hasDetails = null;
        return parent::afterLoad();
    }

    /**
     * @param string $message
     * @param array $context
     * @return $this|bool
     */
    public function record($message, $context = [])
    {
        if (empty($message)) {
            return false;
        }

        if (!$this->getId()) {
            $this->addHeader(__('[Not Engine Run]'));
        }

        if ($this->getId()) {
            $this->appendDetails($message);
        }

        if (!empty($context['connector_data'])) {
            $this->getResource()->updateConnectorData($this->getId(), $context['connector_data']);
        }

        return $this;
    }

    /**
     * @param string $path
     * @return $this
     * @throws \Exception
     */
    public function addFilePath($path)
    {
        $this->setLogFile($path);
        $this->saveUsingDirectQuery();
        return $this;
    }

    /**
     * @return string
     */
    protected function getChainStack()
    {
        $chain = '';
        if (!empty($this->getLogger()->getCallStack())) {
            $chain = ' | ' . __('Auto Call Stack : ') . implode(' -> ', $this->getLogger()->getCallStack()) . ' -> '
                . $this->getIntegration()->getProcessCode();
        }

        return $chain;
    }
}
