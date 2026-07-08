<?php

namespace Digidirect\AbstractGiftCard\Service\Config;

use Digidirect\AbstractGiftCard\Service\ConfigInterface;
use Digidirect\AbstractGiftCard\Service\Helper\SubjectReader;

class ConfigValueHandler implements ValueHandlerInterface
{
    /**
     * @var \Digidirect\AbstractGiftCard\Service\ConfigInterface
     */
    private $_configInterface;

    /**
     * @param \Digidirect\AbstractGiftCard\Service\ConfigInterface $configInterface
     */
    public function __construct(
        ConfigInterface $configInterface
    ) {
        $this->_configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {
        return $this->_configInterface->getValue(SubjectReader::readField($subject), $storeId);
    }
}
