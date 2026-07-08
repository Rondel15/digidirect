<?php

namespace Digidirect\AbstractGiftCard\Service\Command;

use Digidirect\AbstractGiftCard\Service\Command;
use Digidirect\AbstractGiftCard\Service\CommandInterface;

class NullCommand implements CommandInterface
{
    /**
     * Null command. Does nothing. Stable.
     *
     * @param array $commandSubject
     *
     * @return null|Command\ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(array $commandSubject)
    {
        return null;
    }
}
