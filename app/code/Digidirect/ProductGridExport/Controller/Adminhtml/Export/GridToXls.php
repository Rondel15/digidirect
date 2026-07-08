<?php

namespace Digidirect\ProductGridExport\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Digidirect\ProductGridExport\Model\Export\ConvertToXls;

class GridToXls extends Action
{
    /**
     * @param Context $context
     * @param ConvertToXls $converter
     * @param FileFactory $fileFactory
     */
    
    public function __construct(
        Context $context,
        protected ConvertToXls $converter,
        protected FileFactory $fileFactory,

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
        $dateCreated = date('YmdHis');
        return $this->fileFactory->create('products_info_as_of_'.$dateCreated.'.xls', $this->converter->getXlsFile(), 'var');
    }

}
