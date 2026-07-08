<?php
namespace Digidirect\AI\Controller\Adminhtml\Integration;

/**
 * Class DeleteSchedule
 * @package Digidirect\AI\Controller\Adminhtml\Integration
 */
class DeleteSchedule extends RunAbstract
{
    /**
     * @var \Digidirect\AI\Model\Integrations\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var array
     */
    protected $result;

    /**
     * Run constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory
     * @param \Digidirect\AI\Helper\Engine $engineHelper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory,
        \Digidirect\AI\Helper\Engine $engineHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->result = [
            'global' => [
                'error' => false,
                'msg' => '',
            ],
            'data' => [
                'error' => false,
                'msg' => __('Schedule Record has been deleted'),
                'action' => '',
                'schedule_content' => ''
            ]
        ];

        parent::__construct($context, $scheduleFactory, $layoutFactory, $engineHelper);
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AI::process_list');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $processCode = $this->getRequest()->getParam('process_code', false);
        if (!$processCode) {
            return $this->sendWrongCodeResponse();
        }

        $schedule = $this->scheduleFactory->create();
        if (!$this->scheduleFactory->create()->getResource()
            ->load($schedule, $processCode, 'process_code')
            ->delete($schedule)
        ) {
            $this->result['global']['error'] = true;
            $this->result['global']['msg'] = __('Impossible to delete record');
        } else {
            $this->result['global']['msg'] = __('Cron task has been deleted');
        }

        return $this->sendResponse();
    }
}
