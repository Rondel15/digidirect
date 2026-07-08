<?php
namespace Digidirect\ExtendedShippingRates\Controller\Adminhtml\ExtendedShippingRates\Rate;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;

/**
 * Class Upload
 * @package Digidirect\ExtendedShippingRates\Controller\Adminhtml\Shippingrules\Rate
 */
class Upload extends Action
{
    /**
     * Image uploader
     *
     * @var \Digidirect\ExtendedShippingRates\Model\CsvUploader
     */
    protected $csvUploader;

    /**
     * Upload constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Digidirect\ExtendedShippingRates\Model\CsvUploader $csvUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Digidirect\ExtendedShippingRates\Model\CsvUploader $csvUploader
    ) {
        parent::__construct($context);
        $this->csvUploader = $csvUploader;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->csvUploader->saveFileToTmpDir('import_rates');
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
