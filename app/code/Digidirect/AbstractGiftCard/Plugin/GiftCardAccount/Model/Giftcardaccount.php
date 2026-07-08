<?php

namespace Digidirect\AbstractGiftCard\Plugin\GiftCardAccount\Model;

use Magento\GiftCardAccount\Model\Giftcardaccount as MagentoGiftcardaccount;
use Magento\Framework\Exception\NoSuchEntityException;

class Giftcardaccount
{
    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $_giftCardEntityRepository;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $_helper;

    /**
     * Giftcardaccount constructor.
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $repository
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $repository,
        \Digidirect\AbstractGiftCard\Helper\Data $helper
    ) {
        $this->_giftCardEntityRepository = $repository;
        $this->_helper = $helper;
    }

    /**
     * @param MagentoGiftcardaccount $subject
     * @return bool
     */
    public function hasServiceRelation(MagentoGiftcardaccount $subject)
    {
        try {
            $this->_giftCardEntityRepository->loadByGiftCardAccount($subject);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }

    /**
     * @param MagentoGiftcardaccount $subject
     * @param callable $proceed
     * @param bool $saveQuote
     * @param null $quote
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddToCart(
        MagentoGiftcardaccount $subject,
        callable $proceed,
        $saveQuote = true,
        $quote = null
    ) {
        if (!$subject->getAbstractGiftCardEntity() && $this->hasServiceRelation($subject)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You cannot apply this giftcard'));
        }
        return $proceed($saveQuote, $quote);
    }

    /**
     * @param MagentoGiftcardaccount $subject
     * @param callable $proceed
     * @param null $customerId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundRedeem(MagentoGiftcardaccount $subject, callable $proceed, $customerId = null)
    {
        if (!$subject->getAbstractGiftCardEntity() && $this->hasServiceRelation($subject)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You cannot redeem this giftcard'));
        }
        return $proceed($customerId);
    }

    /**
     * @param MagentoGiftcardaccount $subject
     * @param callable $proceed
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundSendEmail(MagentoGiftcardaccount $subject, callable $proceed)
    {
        if (!$subject->getAbstractGiftCardEntity() && $this->hasServiceRelation($subject)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You cannot send email for this giftcard'));
        }
        return $proceed();
    }
}
