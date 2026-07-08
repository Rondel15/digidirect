<?php
namespace Digidirect\ProductOverlay\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Timezone
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Timezone constructor.
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
    }

    /**
     * @return TimezoneInterface
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param string $dateTime
     * @param string $toTz
     * @param string $fromTz
     * @param string $format
     * @return string
     * @throws \Exception
     */
    public function convertToTz($dateTime, $toTz = null, $fromTz = null, $format = self::DEFAULT_DATE_FORMAT)
    {
        if (null === $toTz) {
            $toTz = $this->getTimezone()->getConfigTimezone();
        }
        if (null === $fromTz) {
            $fromTz = $this->getTimezone()->getDefaultTimezone();
        }

        try {
            $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
            $date->setTimezone(new \DateTimeZone($toTz));
            $dateTime = $date->format($format);
            return $dateTime;
        } catch (\Exception $e) {
            throw new LocalizedException(__('Invalid datetime: %1', $dateTime));
        }
    }
}
