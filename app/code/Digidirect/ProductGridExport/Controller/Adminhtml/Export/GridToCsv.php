<?php

namespace Digidirect\ProductGridExport\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Model\Export\ConvertToCsv;

class GridToCsv extends Action
{
    /**
     * @param Context $context
     * @param ConvertToCsv $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        protected ConvertToCsv $converter,
        protected FileFactory $fileFactory
    ){
        parent::__construct($context);
    }

    /**
     * Export data provider to CSV
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $dateCreated    = date('YmdHis');
        return $this->fileFactory->create('products_info_as_of_'.$dateCreated.'.csv', $this->converter->getCsvFile(), 'var');

        // value information nfopen file:
            // $this->_view->loadLayout(false);
            // $dateCreated    = date('YmdHis');
            // $fileName       = 'products_info_as_of_'.$dateCreated.'.csv';
            // $exportBlock    = $this->_view->getLayout()->createBlock('Magento\Catalog\Block\Adminhtml\Product\Grid');
            // $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
            
            // $this->_fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');
            // return $this->_fileFactory->create(
            //     $fileName,
            //     $exportBlock->getCsvFile(),
            //     DirectoryList::VAR_DIR
            // );
    }

}
