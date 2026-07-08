<?php
namespace Digidirect\AI\Controller\Adminhtml\Logs;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Details
 *
 * @package Digidirect\AI\Controller\Adminhtml\Logs
 */
class Details extends \Magento\Backend\App\Action
{
    /**
     * @var \Digidirect\AI\Model\Logger\Types\DbFactory
     */
    protected $loggerFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Details constructor.
     *
     * @param Context $context
     * @param \Digidirect\AI\Model\Logger\Types\DbFactory $loggerFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Digidirect\AI\Model\Logger\Types\DbFactory $loggerFactory,
        TimezoneInterface $timezone
    ) {
        $this->loggerFactory = $loggerFactory;
        parent::__construct($context);
        $this->timezone = $timezone;
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
        $logId = $this->getRequest()->getParam('log_id', false);
        $result = [
            'error' => false,
        ];

        if (!$logId) {
            $result['error'] = true;
            return $this->sendResponse($result);
        }

        /**@var \Digidirect\AI\Model\Logger\Types\Db * */
        $log = $this->loggerFactory->create();
        $log->getResource()->load($log, $logId);
        $log->getResource()->attachData($log);

        $log->setDownloadLink(false);
        if ($log->getLogFile() && file_exists($log->getLogFile())) {
            $log->setDownloadLink($this->getUrl('*/*/downloadLog', ['log_id' => $log->getId()]));
        }

        $connectorData = [];
        foreach ($log->getConnectorData() as $row) {
            $row['request_download_link'] = $row['request_file'] ? $this->getUrl(
                '*/*/downloadFile',
                ['path' => base64_encode($row['request_file'])]
            ) : '';
            $row['response_download_link'] = $row['response_file'] ? $this->getUrl(
                '*/*/downloadFile',
                ['path' => base64_encode($row['response_file'])]
            ) : '';
            $connectorData[] = $row;
        }

        if (count($connectorData)) {
            $log->setData('connector_data', $connectorData);
        } else {
            $log->setData('connector_data', null);
        }

        $data = $log->getData();
        unset($data['log_file']);
        foreach (['created_at', 'updated_at'] as $dateField) {
            $data[$dateField] = $this->timezone->date($data[$dateField])->format('Y-m-d H:i:s');
        }

        $result['data'] = $data;

        return $this->sendResponse($result);
    }

    /**
     * @param mixed $result
     * @return mixed
     */
    protected function sendResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
