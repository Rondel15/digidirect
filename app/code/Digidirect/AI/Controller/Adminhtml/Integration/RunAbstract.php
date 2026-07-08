<?php
namespace Digidirect\AI\Controller\Adminhtml\Integration;

/**
 * Class RunAbstract
 * @package Digidirect\AI\Controller\Adminhtml\Integration
 */
abstract class RunAbstract extends \Magento\Backend\App\Action
{
    /**
     * @var array
     */
    protected $result = [];

    /**
     * @var \Digidirect\AI\Model\Integrations\ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Digidirect\AI\Helper\Engine
     */
    protected $engineHelper;

    /**
     * RunAbstract constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Digidirect\AI\Helper\Engine $engineHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Digidirect\AI\Model\Integrations\ScheduleFactory $scheduleFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Digidirect\AI\Helper\Engine $engineHelper
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->layoutFactory = $layoutFactory;
        $this->engineHelper = $engineHelper;
        parent::__construct($context);
    }

    /**
     * @param mixed $result
     * @return mixed
     */
    protected function sendResponse($result = false)
    {
        if (!$result) {
            $result = $this->result;
        }
        return $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }

    /**
     * @return mixed
     */
    protected function sendWrongCodeResponse()
    {
        $this->result['global']['error'] = true;
        $this->result['global']['msg'] = __('Wrong Process Code');
        return $this->sendResponse($this->result);
    }

    /**
     * @param string $processCode
     * @return string
     */
    protected function getScheduleBlock($processCode)
    {
        $schedule = $this->scheduleFactory->create();
        $schedule->getResource()
            ->load($schedule, $processCode, 'process_code');

        if (!$schedule->getId()) {
            return '';
        }

        $resultBlock = $this->layoutFactory
            ->create()
            ->createBlock('\Digidirect\AI\Block\Adminhtml\Integrations\ScheduleInfo')
            ->setScheduleObj($schedule)
            ->toHtml();

        return $resultBlock;
    }
}
