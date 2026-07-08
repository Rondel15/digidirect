<?php
namespace Digidirect\AddressVerification\Controller\Adminhtml\CountryAddress;

use Digidirect\AddressVerification\Api\CountryAddressAttributeRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\Store;

/**
 * Class Delete
 * @package Digidirect\AddressVerification\Controller\Adminhtml\CountryAddress
 */
class Delete extends Action
{
    /**
     * @var CountryAddressAttributeRepositoryInterface
     */
    protected $countryAddressAttributeRepository;

    /**
     * Delete constructor.
     * @param Context $context
     * @param CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository
     */
    public function __construct(
        Action\Context $context,
        CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository
    ) {
        parent::__construct($context);
        $this->countryAddressAttributeRepository = $countryAddressAttributeRepository;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Digidirect\AddressVerification\Model\CountryAddressAttribute $model */
                $model = $this->countryAddressAttributeRepository->getById($id);
                if ($model->getId()) {
                    $this->countryAddressAttributeRepository->deleteById($model->getId());
                    $this->messageManager->addSuccessMessage(__('Item has been deleted.'));
                } else {
                    $this->messageManager->addWarningMessage(__('Item not found.'));
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an item to delete.'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check if Is allowed to delete
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AddressVerification::address_verification');
    }
}
