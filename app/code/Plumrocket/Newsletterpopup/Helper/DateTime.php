<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2022 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

declare(strict_types=1);

namespace Plumrocket\Newsletterpopup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * @since 4.3.0
 */
class DateTime extends AbstractHelper
{

    /**
     * Format date time.
     *
     * @param \IntlCalendar|\DateTimeInterface|array|string|int|float $dateTime
     * @param string                                                  $format
     * @return string
     */
    public function format($dateTime, string $format): string
    {
        $formatter = new \IntlDateFormatter(
            '',
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            null,
            null,
            $format
        );
        return (string) $formatter->format($dateTime);
    }
}
