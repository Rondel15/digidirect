<?php

namespace Digidirect\AI\Controller\Adminhtml\Logs;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class DownloadLog
 * @package Digidirect\AI\Controller\Adminhtml\Logs
 */
class DownloadLog extends \Magento\Backend\App\Action
{
    /**
     * @var \Digidirect\AI\Model\Logger\Types\DbFactory
     */
    protected $loggerFactory;

    /**
     * FileFactory
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * DownloadLog constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Digidirect\AI\Model\Logger\Types\DbFactory $loggerFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Digidirect\AI\Model\Logger\Types\DbFactory $loggerFactory
    ) {
        $this->loggerFactory = $loggerFactory;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context);
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
     * @param string $fileName
     * @return string
     */
    protected function getDownloadedFileName($fileName)
    {
        $dateDir = dirname($fileName);
        $processCodeDir = dirname($dateDir);
        return sprintf('%s_%s_%s', basename($processCodeDir), basename($dateDir), basename($fileName));
    }

    /**
     * Download backup action
     *
     * @return void|\Magento\Backend\App\Action
     */
    public function execute()
    {
        $logId = $this->getRequest()->getParam('log_id', false);
        if (!$logId) {
            $this->messageManager->addErrorMessage('Log id is not specified');
            $this->_redirectToHome();
            return;
        }

        $log = $this->loggerFactory->create();
        $log->getResource()->load($log, $logId);
        $logFile = $log->getLogFile();

        try {
            if (!$logFile) {
                throw new LocalizedException(__('Database log record has no log file'));
            }
            if (!is_file($logFile)) {
                throw new LocalizedException(__('File %1 does not exist.', $logFile));
            }
            if (!is_readable($logFile)) {
                throw new LocalizedException(__('File %1 is not readable.', $logFile));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_redirectToHome();
            return;
        }

        $this->_fileFactory->create(
            $this->getDownloadedFileName($logFile),
            file_get_contents($logFile),
            DirectoryList::VAR_DIR
        );
    }

    /**
     * Redirect to home page
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function _redirectToHome()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }
}
