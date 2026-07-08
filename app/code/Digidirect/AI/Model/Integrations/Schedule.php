<?php
namespace Digidirect\AI\Model\Integrations;

use Digidirect\AI\Api\Data\ScheduleInterface;

class Schedule extends \Magento\Framework\Model\AbstractModel implements ScheduleInterface
{
    const STATUS_PENDING = 'waiting';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_ERROR = 'Error';

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AI\Model\ResourceModel\Integrations\Schedule');
    }

    /**
     * @return string
     */
    public function getProcessCode()
    {
        return $this->getData(static::PROCESS_CODE);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setProcessCode($value)
    {
        $this->setData(static::PROCESS_CODE, $value);
        return $this;
    }

    /**
     * @return array
     */
    public function getRunOptions()
    {
        return unserialize($this->getData(static::RUN_OPTIONS));
    }

    /**
     * @param string|array $value
     * @return $this
     */
    public function setRunOptions($value)
    {
        if (is_array($value)) {
            ksort($value);
            $value = serialize($value);
        }
        $this->setData(static::RUN_OPTIONS, $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->getData(static::CREATED);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCreated($value)
    {
        $this->setData(static::CREATED, $value);
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->getData(static::COMMENT);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setComment($value)
    {
        $this->setData(static::COMMENT, $value);
        return $this;
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsAddedByAdmin()
    {
        return (bool)$this->getData(static::IS_ADDED_BY_ADMIN);
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setIsAddedByAdmin($value)
    {
        $this->setData(static::IS_ADDED_BY_ADMIN, (bool)$value);
        return $this;
    }
}
