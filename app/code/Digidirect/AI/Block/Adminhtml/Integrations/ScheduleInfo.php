<?php
namespace Digidirect\AI\Block\Adminhtml\Integrations;

/**
 * Class ScheduleInfo
 * @package Digidirect\AI\Block\Adminhtml\Integrations
 */
class ScheduleInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var null
     */
    protected $schedule = null;

    /**
     * Init constructor
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate('integrations/schedule_info.phtml');
        parent::_construct();
    }

    /**
     * @param string $schedule
     * @return $this
     */
    public function setScheduleObj($schedule)
    {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * @return \Digidirect\AI\Model\Integrations\Schedule
     */
    public function getScheduleObj()
    {
        return $this->schedule;
    }
}
