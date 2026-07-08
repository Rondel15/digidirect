<?php

namespace Digidirect\AI\Controller\Adminhtml\Integration;

use Digidirect\AI\Api\Data\ScheduleInterface;

/**
 * Class Run
 * @package Digidirect\AI\Controller\Adminhtml\Integration
 */
class Run extends RunAbstract
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $sysLog;

    /**
     * @var \Digidirect\AI\Model\Engine\EngineFactory
     */
    protected $engineFactory;

    /**
     * @var string
     */
    protected $scheduleFactory;

    /**
     * @var \Digidirect\AI\Model\Integrations\ScheduleRepository
     */
    protected $scheduleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Digidirect\AI\Helper\Logger
     */
    protected $loggerHelper;

    /**
     * @var array
     */
    protected $result;

    /**
     * Run constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Digidirect\AI\Model\Engine\EngineFactory $engineFactory
     * @param \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory
     * @param \Digidirect\AI\Model\Integrations\ScheduleRepository $scheduleRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Digidirect\AI\Helper\Logger $loggerHelper
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Digidirect\AI\Helper\Engine $engineHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Digidirect\AI\Model\Engine\EngineFactory $engineFactory,
        \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory,
        \Digidirect\AI\Model\Integrations\ScheduleRepository $scheduleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Digidirect\AI\Helper\Logger $loggerHelper,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Digidirect\AI\Helper\Engine $engineHelper
    ) {
        $this->sysLog = $logger;
        $this->engineFactory = $engineFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleRepository = $scheduleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->loggerHelper = $loggerHelper;
        $this->layoutFactory = $layoutFactory;

        $this->result = [
            'global' => [
                'error' => false,
                'msg' => __('Process successfully finished'),
            ],
            'data' => [
                'error' => false,
                'msg' => '',
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
     * @return void
     */
    public function execute()
    {
        $processCode = $this->getRequest()->getParam('process_code', false);
        $runOptions = $this->getRequest()->getParam('runoptions', []);
        $isSchedule = $this->getRequest()->getParam('run-separate-process', false);

        if (!$processCode) {
            $this->sendWrongCodeResponse();
            return;
        }

        /* Add process run to cron task only. */
        if ($isSchedule) {
            $this->scheduleRun($processCode, $runOptions);
        } else {
            $runResult = $this->engineFactory
                ->create(
                    [
                        'processCode' => $processCode,
                        'initiator' => 'Web Admin',
                        'runOptions' => $runOptions,
                        'isAdminRun' => true,
                    ]
                )
                ->run();

            if (!$runResult->getResult()) {
                $this->result['global']['error'] = true;
                $this->result['global']['msg'] = $runResult->getMessage();
            }

            $this->result['data']['schedule_content'] = $this->getScheduleBlock($processCode);
        }

        $this->sendResponse($this->result);
    }

    /**
     * @param string $processCode
     * @param array $runOptions
     * @return $this
     */
    protected function scheduleRun(
        $processCode,
        $runOptions
    ) {
        ksort($runOptions);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ScheduleInterface::PROCESS_CODE, $processCode)
            ->addFilter(ScheduleInterface::RUN_OPTIONS, serialize($runOptions))
            ->setPageSize(1)
            ->create();
        $list = $this->scheduleRepository->getList($searchCriteria);
        if ($list->getTotalCount()) {
            $this->result['global']['msg'] = __('The same scheduled run already exists.');
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_PROCEED_HANDLE;
            $this->result['data']['schedule_content'] = $this->getScheduleBlock($processCode);
            return $this;
        }

        /**
         * @var $schedule \Digidirect\AI\Model\Integrations\Schedule
         */
        $schedule = $this->scheduleFactory->create();
        $schedule->setProcessCode($processCode);
        $schedule->setRunOptions($runOptions);
        $schedule->setCreated($this->loggerHelper->getNow());
        $schedule->setComment(\Digidirect\AI\Model\Integrations\Schedule::STATUS_PENDING);
        $schedule->setIsAddedByAdmin(true);

        try {
            $this->scheduleRepository->save($schedule);
            $this->result['global']['msg'] = __('Process have been added to cron task.');
            $this->result['data']['action'] = \Digidirect\AI\Helper\Engine::LOG_INFO_PROCEED_HANDLE;
            $this->result['data']['schedule_content'] = $this->getScheduleBlock($processCode);
        } catch (\Throwable $e) {
            $this->result['global']['error'] = true;
            $this->result['global']['msg'] = __('Could not create scheduled run: %1', $e->getMessage());
        }

        return $this;
    }
}
