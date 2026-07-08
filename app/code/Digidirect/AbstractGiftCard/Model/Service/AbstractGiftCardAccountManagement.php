<?php

namespace Digidirect\AbstractGiftCard\Model\Service;

use Digidirect\AbstractGiftCard\Api\AbstractGiftCardAccountManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class GiftCardAccountManagement
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractGiftCardAccountManagement implements AbstractGiftCardAccountManagementInterface
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\GiftCardAccount\Helper\Data
     */
    protected $_giftCardHelper;

    /**
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory
     */
    protected $_giftCardAccountFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Digidirect\AbstractGiftCard\Model\ServiceInterface
     */
    protected $_serviceInstance;

    /**
     * AbstractGiftCardAccountManagement constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\GiftCardAccount\Helper\Data $giftCardHelper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftCardAccountFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\GiftCardAccount\Helper\Data $giftCardHelper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftCardAccountFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Digidirect\AbstractGiftCard\Helper\Data $helper
    ) {
        $this->_quoteRepository = $quoteRepository;
        $this->_giftCardHelper = $giftCardHelper;
        $this->_giftCardAccountFactory = $giftCardAccountFactory;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $abstractGiftCardEntity
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initService(\Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $abstractGiftCardEntity)
    {
        if ($abstractGiftCardEntity->getServiceCode()) {
            $serviceInstance = $this->getServiceInstanceByCode($abstractGiftCardEntity->getServiceCode());
            $serviceInstance->setAbstractGiftCardEntity($abstractGiftCardEntity);
            return $serviceInstance;
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request Data'));
    }

    /**
     * @param string $instanceCode
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getServiceInstanceByCode($instanceCode)
    {
        if (!$this->_serviceInstance) {
            try {
                $this->_serviceInstance = $this->_helper->getServiceInstance($instanceCode);
            } catch (\UnexpectedValueException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Service Used'));
            }
        }

        return $this->_serviceInstance;
    }

    /**
     * @param int $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function addAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    ) {
        $serviceInstance = $this->_initService($giftCardAccountData);
        if ($serviceInstance->canCheckStatus()) {
            $serviceInstance->validate()->checkStatus();
        }
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->_quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        if (!$serviceInstance->getGiftCardAccount()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong with processing a Gift Card. Please try again later')
            );
        }
        try {
            $serviceInstance->getGiftCardAccount()->addToCart(true, $quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not add gift card code'));
        }
        return true;
    }

    /**
     * @param int $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @return float
     * @throws LocalizedException
     */
    public function checkAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    ) {
        $quote = $this->_quoteRepository->getActive($cartId);
        $serviceInstance = $this->_initService($giftCardAccountData);

        try {
            if ($serviceInstance->canCheckStatus()) {
                $serviceInstance->validate()->checkStatus();
            }
        } catch (\Exception $e) {
            if ($e instanceof LocalizedException) {
                throw $e;
            } else {
                throw new LocalizedException(__('Please correct the wrong or expired Gift Card Code.'), $e);
            }
        }

        if (!$serviceInstance->getGiftCardAccount()) {
            throw new LocalizedException(
                __('Something went wrong with processing a Gift Card. Please try again later')
            );
        }
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->_storeManager->getStore()->getBaseCurrency();
        return $currency->convert($serviceInstance->getGiftCardAccount()->getBalance(), $quote->getQuoteCurrencyCode());
    }
}
