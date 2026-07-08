<?php

namespace Digidirect\AI\Model\Logger\Types;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Email extends \Magento\Framework\DataObject implements TypesInterface
{
    const CONFIG_EMAIL = 'trans_email/ident_support/email';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $subject;

    /**
     * Email constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param string $email
     * @param string $subject
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $email = null,
        $subject = null,
        array $data = []
    ) {
        parent::__construct($data);
        $this->scopeConfig = $scopeConfig;
        $this->email = $email ?? $this->scopeConfig->getValue(static::CONFIG_EMAIL, ScopeInterface::SCOPE_STORE);
        $this->subject = $subject ?? __('Integration Log');
    }

    /**
     * @return bool
     */
    public function getRecordIdentifier()
    {
        return false;
    }

    /**
     * @param string $message
     * @param string $level
     * @return $this
     */
    public function addHeader($message, $level = null)
    {
        return $this;
    }

    /**
     * @param string|array $identifiers
     * @return $this
     * @throws \Exception
     */
    public function addIdentifyingParams($identifiers)
    {
        return $this;
    }

    /**
     * Commit a message
     *
     * @param string $message
     * @param array $data
     * @return int|bool
     */
    public function record($message, $data = [])
    {
        //TODO: Use Magento Email mechanism.
        mail($this->email, $this->subject, $message);
        return true;
    }
}
