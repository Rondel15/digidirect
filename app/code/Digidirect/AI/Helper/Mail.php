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
class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_FAIL_RUN_EMAIL_TEMPLATE = 'digidirect_ai/exceptions/fail_run_email_template';
    const XML_PATH_FAIL_RUN_EMAILS = 'digidirect_ai/exceptions/exceptions_emails';
    const XML_PATH_QUEUE_FAIL_EMAILS = 'digidirect_ai/queue/fail_emails';
    const XML_PATH_QUEUE_FAIL_EMAIL_TEMPLATE = 'digidirect_ai/queue/fail_email_template';

    /**
     * Scope Config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Global Fail Email Template
     *
     * @var string
     */
    protected $globalFailEmailTemplate = 'digidirect_ai_exceptions_global_fail_email_template';

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

    /**
     * Get fail email template
     *
     * @param string $globalFailTemplate
     * @return mixed|string
     */
    public function _getFailEmailTemplate($globalFailTemplate)
    {
        if ($globalFailTemplate) {
            return $this->globalFailEmailTemplate;
        }

        $configValue = $this->scopeConfig->getValue(self::XML_PATH_FAIL_RUN_EMAIL_TEMPLATE);
        return $configValue;
    }

    /**
     * @param string $emails
     * @return array
     */
    protected function getEmails($emails)
    {
        $emails = explode(',', trim($emails));
        $emails = array_filter($emails, 'trim');
        $emails = array_unique($emails);
        return $emails;
    }

    /**
     * Get fail emails
     *
     * @return mixed
     */
    public function getFailEmails()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_FAIL_RUN_EMAILS);
        return $this->getEmails($configValue);
    }

    /**
     * @return array
     */
    public function getQueueFailEmails()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_QUEUE_FAIL_EMAILS);
        return $this->getEmails($configValue);
    }

    /**
     * @return string|int
     */
    public function getQueueFailEmailTemplate()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_PATH_QUEUE_FAIL_EMAIL_TEMPLATE);
        return $configValue;
    }
}
