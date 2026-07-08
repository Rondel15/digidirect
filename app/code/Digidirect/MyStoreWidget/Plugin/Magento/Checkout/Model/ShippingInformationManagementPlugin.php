<?php
namespace Digidirect\MyStoreWidget\Plugin\Magento\Checkout\Model;

use Magento\Checkout\Model\ShippingInformationManagement as Subject;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\MyStoreWidget\Helper\Data as Helper;
use Digidirect\MyStoreWidget\Helper\Config as ConfigHelper;
use Digidirect\Utilities\Helper\Message as MessageHelper;

class ShippingInformationManagementPlugin
{
    /**
     * @var MyStoreRepositoryInterface
     */
    protected $myStoreRepository;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ConfigHelper
     */
    protected $messageHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * ShippingInformationManagementPlugin constructor.
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param Helper $helper
     * @param ConfigHelper $configHelper
     * @param MessageHelper $messageHelper
     * @param Session $checkoutSession
     */
    public function __construct(
        MyStoreRepositoryInterface $myStoreRepository,
        Helper $helper,
        ConfigHelper $configHelper,
        MessageHelper $messageHelper,
        Session $checkoutSession
    ) {
        $this->myStoreRepository = $myStoreRepository;
        $this->helper = $helper;
        $this->configHelper = $configHelper;
        $this->messageHelper = $messageHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param Subject $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws LocalizedException
     * @return array
     */
    public function beforeSaveAddressInformation(
        Subject $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        
//        if ($this->configHelper->verifyShippingAddressOnCheckout()) {
//            /** @var DataObject $address */
//            $address = $addressInformation->getShippingAddress();
//            $storeByAddress = $this->myStoreRepository->getStoreByAddress($address);
//            if (!$storeByAddress) {
//                throw new LocalizedException(__(
//                    $this->getMessage('There is no Store match your Shipping Address. Please, check entered data.')
//                ));
//            }
//
//            $currentStore = $this->helper->getCurrentStore();
//            if (!$currentStore || ($currentStore->getId() != $storeByAddress->getId())) {
//                $this->helper->setCurrentStore($storeByAddress->getId());
//
//                $quote = $this->checkoutSession->getQuote();
//                if ($quote->getId()) {
//                    $quote->collectTotals()->save();
//                }
//
//                $message = 'Your cart has been changed according to your Shipping Address. Please check your Cart.';
//                throw new LocalizedException(__($this->getMessage($message)));
//            }
//        }
        
        return [$cartId, $addressInformation];
    }

    /**
     * @param string $message
     * @return string
     */
    protected function getMessage($message)
    {
        return $this->messageHelper->getCustomizedMessage($message);
    }
}
