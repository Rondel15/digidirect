<?php
namespace Digidirect\AddressVerification\Controller\Adminhtml\CountryAddress;

use Digidirect\AddressVerification\Api\CountryAddressAttributeRepositoryInterface;
use Magento\Backend\App\Action;
use Digidirect\AddressVerification\Model\CountryAddressAttributeRepository;
use Magento\Store\Model\Store;
use Digidirect\AddressVerification\Helper\Aupost;

/**
 * Class Save
 * @package Digidirect\AddressVerification\Controller\Adminhtml\CountryAddress
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var CountryAddressAttributeRepository
     */
    protected $countryAddressAttributeRepository;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * @var Aupost
     */
    protected $aupostHelper;

    /**
     * Save constructor.
     * @param Action\Context $context
     * @param CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository
     * @param Aupost $aupostHelper
     */
    public function __construct(
        Action\Context $context,
        CountryAddressAttributeRepositoryInterface $countryAddressAttributeRepository,
        Aupost $aupostHelper
    ) {
        $this->countryAddressAttributeRepository = $countryAddressAttributeRepository;
        $this->aupostHelper = $aupostHelper;
        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('entity_id', 0);
        $postData = $this->getRequest()->getPostValue();

        if ($postData) {
            try {
                $model = $this->countryAddressAttributeRepository->getById($id);
                if (!$model->getId()) {
                    $postData['entity_id'] = null;
                }
                $model->setData($postData);
                $this->_eventManager->dispatch(
                    'country_address_attribute_prepare_save',
                    ['model' => $model, 'request' => $this->getRequest()]
                );
                $this->countryAddressAttributeRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved item'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('entity_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check permissions for this action
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Digidirect_AddressVerification::address_verification');
    }
}
