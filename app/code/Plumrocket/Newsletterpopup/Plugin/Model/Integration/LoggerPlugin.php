<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */
declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Plugin\Model\Integration;

/**
 * @since 4.3.0
 */
class LoggerPlugin
{
    /**
     * Before Plugin for input parameters formation of method addRecord and compatibility with Magento 2.4.4
     *
     * @param \Plumrocket\Newsletterpopup\Model\Integration\Logger $subject
     * @param int                                                  $level
     * @param string                                               $message
     * @param array                                                $context
     * @return array
     */
    public function beforeAddRecord(
        \Plumrocket\Newsletterpopup\Model\Integration\Logger $subject,
        $level,
        $message,
        array $context = []
    ) {
        try {
            $levelName = \Monolog\Logger::getLevelName($level);
        } catch (\Exception $e) {
            $levelName = $level;
        }

        $message = sprintf(
            '%s response %s: %s',
            mb_strtoupper($subject->getIntegrationName()),
            $levelName,
            $message
        );

        return [$level, $message, $context];
    }
}
