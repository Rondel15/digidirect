<?php
namespace Digidirect\Digi\Plugin\Magento\Ui\Component\Form\Element\DataType;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Date
 * @package Digidirect\Digi\Plugin\Magento\Ui\Component\Form\Element\DataType
 */
class Date
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var array
     */
    private $listWrongLocale=[];

    /**
     * Date constructor.
     * @param TimezoneInterface $localeDate
     * @param array $listWrongLocale
     */
    public function __construct(TimezoneInterface $localeDate, array $listWrongLocale)
    {
        $this->localeDate = $localeDate;
        $this->listWrongLocale = $listWrongLocale;
    }

    /**
     * @param \Magento\Ui\Component\Form\Element\DataType\Date $subject
     * @param $result
     * @param $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @param bool $setUtcTimeZone
     * @return \DateTime|null
     */
    public function afterConvertDate(
        \Magento\Ui\Component\Form\Element\DataType\Date $subject,
        $result,
        $date,
        $hour = 0,
        $minute = 0,
        $second = 0,
        $setUtcTimeZone = true
    ) {
        if (!in_array($subject->getLocale(), $this->getListWrongLocale())){
            return $result;
        }
        try {
            $dateObj = $this->localeDate->date(
                $date,
                $subject->getLocale(),
                true
            );
            $dateObj->setTime($hour, $minute, $second);
            if ($setUtcTimeZone) {
                $dateObj->setTimezone(new \DateTimeZone('UTC'));
            }
            return $dateObj;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getListWrongLocale(){
        return $this->listWrongLocale;
    }
}