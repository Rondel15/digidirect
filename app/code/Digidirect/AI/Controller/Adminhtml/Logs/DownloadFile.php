<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Digidirect\AI\Controller\Adminhtml\Logs;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class DownloadFile
 * @package Digidirect\AI\Controller\Adminhtml\Logs
 */
class DownloadFile extends \Magento\Backend\App\Action
{
    /**
     * Result Raw Factory
     *
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $_resultRawFactory;

    /**
     * FileFactory
     *
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct(
            $context
        );
        $this->_resultRawFactory = $resultRawFactory;
        $this->_fileFactory = $fileFactory;
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
     * Download backup action
     *
     * @return \Magento\Backend\App\Action
     */
    public function execute()
    {
        if (!$filePath = $this->getRequest()->getParam('path', false)) {
            $this->_redirectToHome();
        }
        try {
            $filePath = base64_decode($filePath);
            $fileName = basename($filePath);
            return $this->_fileFactory->create(
                $fileName,
                file_get_contents($filePath),
                DirectoryList::VAR_DIR
            );
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Sorry. We can`t open the file. (%1)', $e->getMessage()));
        }

        return $this->_redirectToHome();
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
