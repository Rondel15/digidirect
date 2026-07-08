<?php

namespace Digidirect\AbstractGiftCard\Api;

use Psr\Log\LoggerInterface;
use Monolog\Logger as MonologLogger;

interface AbstractGiftCardLoggerInterface
{
    /**
     * @param array|string $data
     * @param int|string $level
     * @param array|null $maskKeys
     * @param bool|null $forceDebug
     * @return void
     */
    public function debug($data, $level = MonologLogger::DEBUG, array $maskKeys = null, $forceDebug = null);

    /**
     * @return bool
     */
    public function isDebugOn();

    /**
     * @param string $code
     * @return $this
     */
    public function setCommandCode($code);

    /**
     * @return string
     */
    public function getCommandCode();
}
