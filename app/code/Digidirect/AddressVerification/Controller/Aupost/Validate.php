<?php
namespace Digidirect\AddressVerification\Controller\Aupost;

/**
 * Class Validate
 * @package Digidirect\AddressVerification\Controller\Aupost
 */
class Validate extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Digidirect\AddressVerification\Helper\Aupost
     */
    protected $aupostHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Search constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Digidirect\AddressVerification\Helper\Aupost $aupostHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Digidirect\AddressVerification\Helper\Aupost $aupostHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->aupostHelper = $aupostHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $postcode = $this->getRequest()->getParam('postcode');
        $suburb = $this->getRequest()->getParam('city');
        $state = $this->getRequest()->getParam('region');
        $stateId = $this->getRequest()->getParam('region_id');
        $countryCode = $this->getRequest()->getParam('country_code');
        $isValid = $this->aupostHelper->isCombinationValid($countryCode, $postcode, $suburb, $state, $stateId);
        $result = [
            'validate' => $isValid,
            'message' => (
            !$isValid ? __('You entered invalid Postcode and/or Suburb. Please, check and try again') : ''
            )
        ];
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
