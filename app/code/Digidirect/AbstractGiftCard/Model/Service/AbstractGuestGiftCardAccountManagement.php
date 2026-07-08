<?php

namespace Digidirect\AbstractGiftCard\Model\Service;

use Digidirect\AbstractGiftCard\Api\AbstractGuestGiftCardAccountManagementInterface;
use Magento\GiftCardAccount\Model\GuestCart\GiftCardAccountManagement;
use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Class AbstractGuestGiftCardAccountManagement
 */
class AbstractGuestGiftCardAccountManagement extends GiftCardAccountManagement implements
    AbstractGuestGiftCardAccountManagementInterface
{
    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardAccountManagementInterface
     */
    protected $_abstractGiftCartAccountManagement;

    /**
     * AbstractGuestGiftCardAccountManagement constructor.
     * @param \Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface $giftCartAccountManagement
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardAccountManagementInterface $abstractGiftCardAccountManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Magento\GiftCardAccount\Api\GiftCardAccountManagementInterface $giftCartAccountManagement,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardAccountManagementInterface $abstractGiftCardAccountManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct($giftCartAccountManagement, $quoteIdMaskFactory);
        $this->_abstractGiftCartAccountManagement = $abstractGiftCardAccountManagement;
    }

    /**
     * @param string $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @return bool
     */
    public function addAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->_abstractGiftCartAccountManagement
            ->addAbstractGiftCard($quoteIdMask->getQuoteId(), $giftCardAccountData);
    }

    /**
     * @param string $cartId
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return float
     */
    public function checkAbstractGiftCard(
        $cartId,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $giftCardAccountData
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->_abstractGiftCartAccountManagement->checkAbstractGiftCard(
            $quoteIdMask->getQuoteId(),
            $giftCardAccountData
        );
    }

    /**
     * @param string $cartId
     * @param \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface $giftCardAccountData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return bool
     */
    public function addGiftCard(
        $cartId,
        \Magento\GiftCardAccount\Api\Data\GiftCardAccountInterface $giftCardAccountData
    ) {
        return false;
    }

    /**
     * @param string $cartId
     * @param string $giftCardCode
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return bool
     */
    public function checkGiftCard($cartId, $giftCardCode)
    {
        return false;
    }
}
