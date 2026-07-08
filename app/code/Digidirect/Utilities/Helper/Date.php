<?php
namespace Digidirect\Utilities\Helper;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Date
 * @package Digidirect\Utilities\Helper
 */
class Date
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Date constructor.
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * Convert date to default timezone
     *
     * @param string $sourceDate
     * @param string|null $format
     * @return string
     */
    public function convertDateToDefaultTimezone(string $sourceDate, $format = null)
    {
        $configTimezone = new \DateTimeZone($this->getConfigTimezone());
        $dateConfigTimezone = new \DateTime($sourceDate, $configTimezone);

        $defaultTimezone = new \DateTimeZone($this->getDefaultTimezone());
        $convertedDate = $dateConfigTimezone->setTimezone($defaultTimezone);

        if ($format) {
            return $convertedDate->format($format);
        }

        return $convertedDate->format(DateTime::DATETIME_PHP_FORMAT);
    }

    /**
     * Retrieve configuration timezone
     *
     * @return \DateTimeZone
     */
    public function getConfigTimezone()
    {
        return $this->timezone->getConfigTimezone();
    }

    /**
     * Retrieve default timezone
     *
     * @return \DateTimeZone
     */
    public function getDefaultTimezone()
    {
        return $this->timezone->getDefaultTimezone();
    }
}
