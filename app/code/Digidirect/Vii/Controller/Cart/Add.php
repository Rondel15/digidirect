<?php

namespace Digidirect\Vii\Controller\Cart;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\GiftCardAccount\Model\Giftcardaccount;

/**
 * Class Add
 * @package Digidirect\Vii\Controller\Cart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Digidirect\AbstractGiftCard\Controller\AbstractController
{
    /**
     * @var \Magento\GiftCardAccount\Helper\Data
     */
    protected $giftCAHelper;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * @var \Digidirect\Vii\Helper\Data
     */
    protected $helperData;

    /**
     * Add constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Helper\Data $helperData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Helper\Data $helperData
    ) {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart, $helper);
        $this->giftCAHelper = $giftCAHelper;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->helperData = $helperData;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_checkoutSession->getQuoteId()) {
            $exception = new \Exception(__('We can\'t apply this gift card.'));
            $this->messageManager->addExceptionMessage($exception);
            return $this->_goBack();
        }
        try {
            $serviceInstance = $this->_initService();
            $quote = $this->_checkoutSession->getQuote();
            $cards = $this->giftCAHelper->getCards($quote);
            if (!empty($cards) && $entity = $serviceInstance->getAbstractGiftCardEntity()) {
                foreach ($cards as $card) {
                    if ($card[Giftcardaccount::CODE] == $entity->getCode()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('This gift card account is already in the quote.')
                        );
                    }
                }
            }

            if ($serviceInstance->canHold()) {
                try {
                    
                    /**
                    * Updated by Johnry Valeriano, Aug. 26, 2010
                    * Used total current balance for Base Amount so the system can use all current balance when price is updating.
                    */

                    $serviceInstance->validate()->checkStatus();
                    $availableBalance = $serviceInstance->getGiftCardAccount()->getBalance();
                    
                    $serviceInstance->validate()->hold($availableBalance);
                    if ($this->helperData->isCustomerAsGuest($quote)) {
                        $this->helperData->saveVisitorData($quote);
                    }
                } catch (LocalizedException $e) {
                    $availableBalance = $serviceInstance->getAvailableBalance();
                    if (!$availableBalance) {
                        throw new LocalizedException(__($e->getMessage()));
                    }
                    // try to hold only available balance
                    $serviceInstance->validate()->hold($availableBalance);
                    if ($this->helperData->isCustomerAsGuest($quote)) {
                        $this->helperData->saveVisitorData($quote);
                    }
                }
            }
            if (!$serviceInstance->getGiftCardAccount()) {
                $this->messageManager->addError(
                    'Something went wrong with processing a Gift Card. Please try again later'
                );
                return $this->_goBack();
            }
            $serviceInstance->getGiftCardAccount()->addToCart();
            $this->messageManager->addSuccess(__('Gift Card was added.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            if ($serviceInstance->getLastToken() && $serviceInstance->canCancel()) {
                $serviceInstance->setStore($quote->getStoreId());
                $serviceInstance->validate()->cancel($e->getMessage(), $serviceInstance->getLastToken());
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot apply this gift card.'));
            if ($serviceInstance->getLastToken() && $serviceInstance->canCancel()) {
                $serviceInstance->setStore($quote->getStoreId());
                $serviceInstance->validate()->cancel($e->getMessage(), $serviceInstance->getLastToken());
            }
        }
        return $this->_goBack();
    }
}
