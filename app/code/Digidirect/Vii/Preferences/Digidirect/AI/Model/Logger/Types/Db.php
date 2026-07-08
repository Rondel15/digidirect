<?php

namespace Digidirect\Vii\Preferences\Digidirect\AI\Model\Logger\Types;

class Db extends \Digidirect\AI\Model\Logger\Types\Db
{
    /**
     * @param string $message
     * @param array $context
     * @return $this|bool
     */
    public function record($message, $context = [])
    {
        $logger = $this->getLogger();
        if (!$logger instanceof \Digidirect\AI\Model\Logger\LoggerInterface) {
            return parent::record($message, $context);
        }

        $integration = $logger->getIntegration();
        if (!$integration instanceof \Digidirect\AI\Model\Integrations\Integrations) {
            return parent::record($message, $context);
        }

        if (!$integration->getProcessCode() || $integration->getProcessCode() != 'AbstractGiftCard') {
            return parent::record($message, $context);
        }

        if ($this->getId() && !$this->getResource()->hasDetails($this)) {
            $message = $this->getData('details') . PHP_EOL . $message;
            $this->setId(null)
                ->setDetails(null);
            $this->hasDetails = false;
            $this->addHeader('');
        }
        return parent::record($message, $context);
    }
}
