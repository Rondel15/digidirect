<?php
namespace Digidirect\AI\Controller\Adminhtml\Integration;

use Magento\Backend\App\Action\Context;

/**
 * Class RunOptions
 * @package Digidirect\AI\Controller\Adminhtml\Integration
 */
class RunOptions extends RunAbstract
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $sysLog;

    /**
     * @var \Digidirect\AI\Model\Integrations\IntegrationsFactory
     */
    protected $integrationFactory;

    /**
     * @var \Digidirect\AI\Model\Integrations\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * RunOptions constructor.
     * @param Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Digidirect\AI\Model\Integrations\IntegrationsFactory $integration
     * @param \Digidirect\AI\Helper\Engine $engineHelper
     * @param \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Digidirect\AI\Model\Integrations\IntegrationsFactory $integration,
        \Digidirect\AI\Helper\Engine $engineHelper,
        \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->sysLog = $logger;
        $this->integrationFactory = $integration;
        $this->scheduleFactory = $scheduleFactory;
        $this->layoutFactory = $layoutFactory;
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
        $integration = $this->integrationFactory->create();
        $integration->getResource()
            ->load($integration, $processCode, 'process_code');

        $this->result = [
            'global' => [
                'error' => false,
                'msg' => ''
            ],
            'data' => [
                'content' => '',
                'can_submit_run' => false,
                'process_code' => $processCode,
                'process_status' => $integration->getStatus(),
                'action' => \Digidirect\AI\Helper\Engine::LOG_INFO_STOP_HANDLE,
                'schedule_content' => ''
            ]
        ];

        if (!$processCode) {
            return $this->sendWrongCodeResponse();
        }

        if ($integration->getStatus() == \Digidirect\AI\Model\Integrations\Integrations::STATUS_PROCESSING
            && !$integration->getData('multiple_run')
        ) {
            $this->result['error'] = true;
            $this->result['data']['content'] = __('Integration currently in process') . ' ......';
            $this->result['data']['can_submit_run'] = false;
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_PROCEED_HANDLE;

            $this->sendResponse($this->result);
        } else {
            $this->result['data']['content'] = $this->layoutFactory
                ->create()
                ->createBlock('Digidirect\AI\Block\Adminhtml\Integrations\Renderer\Options')
                ->setOptions($integration->getRunOptions())
                ->toHtml();
            $this->result['data']['can_submit_run'] = true;
        }

        $scheduleBlock = $this->getScheduleBlock($processCode);
        $this->result['data']['schedule_content'] = $scheduleBlock;
        if (trim($scheduleBlock)) {
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_PROCEED_HANDLE;
        }

        $this->sendResponse($this->result);
    }
}
