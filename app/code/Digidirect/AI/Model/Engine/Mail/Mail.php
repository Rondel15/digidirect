<?php

namespace Digidirect\AI\Model\Engine\Mail;

/**
 * Class Mail
 *
 * @package Digidirect\AI\Helper
 */
class Mail
{
    /**
     * Scope Config Interface
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Directory List
     *
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * LoggerHelper
     *
     * @var \Digidirect\AI\Helper\Mail
     */
    protected $_mailHelper;

    /**
     * LoggerHelper
     *
     * @var \Digidirect\AI\Helper\Logger
     */
    protected $_logHelper;

    /**
     * Inline Translation
     *
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * Transport Builder
     *
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Mail constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Digidirect\AI\Helper\Mail $mailHelper
     * @param \Digidirect\AI\Helper\Logger $logHelper
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Digidirect\AI\Helper\Mail $mailHelper,
        \Digidirect\AI\Helper\Logger $logHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_logHelper = $logHelper;
        $this->_mailHelper = $mailHelper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_directoryList = $directoryList;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param \Digidirect\AI\Model\Logger\Types\Db $log
     * @param array $emails
     * @param bool $attachLogFile
     * @return bool
     */
    public function sendLogEmail(\Digidirect\AI\Model\Logger\Types\Db $log, $emails, $attachLogFile = false)
    {
        if (!count($emails)) {
            return false;
        }

        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($log->getData());

        $this->inlineTranslation->suspend();

        //reset previous data
        $this->transportBuilder->resetObjectState();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($this->_logHelper->getEmailTemplate())
            ->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars(['data' => $postObject])
            ->setFrom(
                $this->_scopeConfig->getValue(
                    \Magento\Contact\Controller\Index::XML_PATH_EMAIL_SENDER,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
                )
            )
            ->addTo($emails);

        $logFile = $log->getLogFile();
        if ($attachLogFile && $logFile && file_exists($logFile)) {
            /**
             * @var $transport \Digidirect\Utilities\Preference\Magento\Framework\Mail\Template\TransportBuilder
             */
            $transport->createAttachment(
                file_get_contents($logFile),
                'application/octet-stream', //we do not use constants: used different Zend libraries in 2.2.7 and 2.3.0
                'attachment',
                'base64',
                basename($logFile)
            );
        }

        $transport->getTransport()->sendMessage();
        $this->inlineTranslation->resume();

        return true;
    }

    /**
     * @param string $msg
     * @param null $log
     * @param bool $globalFailTemplate
     * @return bool
     */
    public function sendFailEmail($msg, $log = null, $globalFailTemplate = false)
    {
        $emails = $this->_mailHelper->getFailEmails();
        $templateId = $this->_mailHelper->_getFailEmailTemplate($globalFailTemplate);
        $dataObject = new \Magento\Framework\DataObject();
        if ($log) {
            $dataObject->setData($log->getData());
        }
        $dataObject->setData('message', $msg);
        $data = ['data' => $dataObject];
        return $this->sendEmail($templateId, $emails, $data);
    }

    /**
     * @param \Digidirect\AI\Api\Data\QueueInterface $queue
     * @param array $runOptions
     * @return bool
     */
    public function sendQueueFailEmail(\Digidirect\AI\Api\Data\QueueInterface $queue, $runOptions)
    {
        $emails = $this->_mailHelper->getQueueFailEmails();
        $templateId = $this->_mailHelper->getQueueFailEmailTemplate();
        $queueObject = new \Magento\Framework\DataObject();
        $queueObject->setData($queue->getData());
        $queueObject->setData('run_options', $this->_logHelper->varExportForLog($runOptions));
        $data = ['queue' => $queueObject];
        return $this->sendEmail($templateId, $emails, $data);
    }

    /**
     * @param int $templateId
     * @param array $emails
     * @param array $data
     * @return bool
     */
    protected function sendEmail($templateId, $emails, $data)
    {
        if (empty($emails)) {
            return false;
        }

        $this->inlineTranslation->suspend();
        $this->transportBuilder->resetObjectState();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($data)
            ->setFrom(
                $this->_scopeConfig->getValue(
                    \Magento\Contact\Controller\Index::XML_PATH_EMAIL_SENDER,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
                )
            )
            ->addTo($emails);

        $transport->getTransport()->sendMessage();
        $this->inlineTranslation->resume();

        return true;
    }
}
