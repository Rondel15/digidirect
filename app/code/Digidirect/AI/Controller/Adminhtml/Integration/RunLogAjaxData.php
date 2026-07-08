<?php
namespace Digidirect\AI\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action\Context;

/**
 * Class getLogData
 * @package Digidirect\AI\Controller\Adminhtml\Integration
 */
class RunLogAjaxData extends RunAbstract
{
    /**
     * @var \Digidirect\AI\Model\Integrations\IntegrationsFactory
     */
    protected $integrationFactory;

    /**
     * @var \Digidirect\AI\Model\Integrations\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * RunLogAjaxData constructor.
     * @param Context $context
     * @param \Digidirect\AI\Model\Integrations\IntegrationsFactory $integration
     * @param \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Digidirect\AI\Helper\Engine $engineHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Digidirect\AI\Model\Integrations\IntegrationsFactory $integration,
        \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Digidirect\AI\Helper\Engine $engineHelper
    ) {
        $this->integrationFactory = $integration;
        $this->scheduleFactory = $scheduleFactory;

        $this->result = [
            'global' => [
                'error' => false,
                'msg' => __('Process Finished. Please check logs for additional information'),
                'blink' => false
            ],
            'data' => [
                'action' => \Digidirect\AI\Helper\Engine::LOG_INFO_STOP_HANDLE,
                'log_msg' => '',
                'log_details' => '',
                'can_submit_run' => true,
                'schedule_content' => '',
                'content' => '',
                'process_status' => ''
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
        return $this->_authorization->isAllowed('Digidirect_AI::logs');
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
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_STOP_HANDLE;
            return $this->sendWrongCodeResponse();
        }

        $integration = $this->integrationFactory->create();
        $integration->getResource()
            ->load($integration, $processCode, 'process_code');

        $scheduleRecord = $this->scheduleFactory->create();
        $scheduleRecord->getResource()
            ->load($scheduleRecord, $processCode, 'process_code');

        $this->result['data']['content'] = $this->layoutFactory
            ->create()
            ->createBlock('Digidirect\AI\Block\Adminhtml\Integrations\Renderer\Options')
            ->setOptions($integration->getRunOptions())
            ->toHtml();

        $this->result['data']['process_status'] = $integration->getStatus();

        if ($integration->getStatus() == \Digidirect\AI\Model\Integrations\Integrations::STATUS_PROCESSING) {
            $this->result['global']['msg'] = __('Process execution...');
            $this->addLastLogInfo($processCode);
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_PROCEED_HANDLE;
            $this->result['data']['can_submit_run'] = false;
            $this->result['data']['content'] = '';
        }

        if ($scheduleRecord->getId()) {
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_PROCEED_HANDLE;
            $this->result['data']['process_status'] = $integration->getStatus();
            $this->result['global']['msg'] = __('Waiting for next cron execution...');
        }

        $this->result['data']['schedule_content'] = $this->getScheduleBlock($processCode);
        return $this->sendResponse($this->result);
    }

    /**
     * @param string $processCode
     * @return mixed
     */
    protected function addLastLogInfo($processCode)
    {
        /**@var \Digidirect\AI\Model\Logger\Logger * */
        $log = $this->_objectManager->create(\Digidirect\AI\Model\ResourceModel\Logger\Logger::class)
            ->getLastLogByProcessCode($processCode);

        $this->result['data']['log_msg'] = isset($log['comment']) ? $log['comment'] : __('N/A');
        $this->result['data']['log_details'] = isset($log['details']) ? $log['details'] : __('N/A');

        return $this->result;
    }
}
