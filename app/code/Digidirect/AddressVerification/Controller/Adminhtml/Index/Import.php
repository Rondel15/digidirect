<?php
namespace Digidirect\AddressVerification\Controller\Adminhtml\Index;

use Digidirect\AddressVerification\Helper\Aupost;
use Digidirect\AddressVerification\Helper\Autocomplete;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\AddressVerification\Model\Import as ImportModel;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class Import
 * @package Digidirect\AddressVerification\Controller\Adminhtml\Index
 */
class Import extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ImportModel
     */
    protected $importModel;

    /**
     * @var Aupost
     */
    protected $aupostHelper;

    /**
     * @var Autocomplete
     */
    protected $autocompleteHelper;

    /**
     * Import constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ImportModel $importModel
     * @param Aupost $helper
     * @param Autocomplete $autocompleteHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ImportModel $importModel,
        Aupost $helper,
        Autocomplete $autocompleteHelper
    ) {
        parent::__construct($context);
        $this->importModel = $importModel;
        $this->aupostHelper = $helper;
        $this->autocompleteHelper = $autocompleteHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $websiteId = $this->getRequest()->getParam('website_id');
        $countryCode = $this->getRequest()->getParam('country_code');
        $response = [
            'message' =>__('File has been successfully imported'),
            'exported' => true,
            'last_import_time' => null
        ];
        try {
            $scopeInfo = $this->autocompleteHelper->getScopeInfo($storeId, $websiteId);
            $this->importModel->processImport(
                $this->aupostHelper->getAuPostFile($scopeInfo->getScopeId(), $scopeInfo->getScopeType()),
                $countryCode,
                $storeId,
                $websiteId
            );
            if ($this->importModel->getLastImportTime()) {
                $response['last_import_time'] = $this->aupostHelper->formatDate(
                    $this->importModel->getLastImportTime()
                );
            }
        } catch (LocalizedException $e) {
            $response['message'] = $e->getMessage();
        } catch (Exception $e) {
            $response['exported'] = false;
            $response['message'] = __('Cannot import file. Some error is occurred');
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * Check if index action is allowed
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AddressVerification::import_file');
    }
}
