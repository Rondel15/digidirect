<?php
namespace Digidirect\AI\Model\Logger;

use Digidirect\AI\Model\Integrations\Integrations as Integration;

/**
 * Interface LoggerInterface
 *
 * @package Digidirect\AI\Model\Logger
 */
interface LoggerInterface extends \PSR\Log\LoggerInterface
{
    /**
     * @param string $logType
     * @return mixed
     */
    public function getLogRecordIdentifier($logType);
}
