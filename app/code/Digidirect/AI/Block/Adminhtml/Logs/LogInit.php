<?php
namespace Digidirect\AI\Block\Adminhtml\Logs;

/**
 * Class LogInit
 * @package Digidirect\AI\Block\Adminhtml\Logs
 */
class LogInit extends \Magento\Backend\Block\Template
{
    /**
     * Init constructor
     * @return void
     */
    public function _construct()
    {
        $this->setTemplate('log/log_init.phtml');
        parent::_construct();
    }

    /**
     * @return string
     */
    public function getRequestLogUrl()
    {
        return $this->getUrl('digidirect_ai/logs/details');
    }

    /**
     * @return string
     */
    public function getSendEmailLogUrl()
    {
        return $this->getUrl('digidirect_ai/logs/sendlogsemail');
    }
}
