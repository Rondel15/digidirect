<?php

namespace Digidirect\AbstractGiftCard\Observer;

use Digidirect\AbstractGiftCard\Exception\BlockOrderPlace;
use Digidirect\AbstractGiftCard\Exception\BlockOrderPlaceException;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Digidirect\AbstractGiftCard\Helper\Data;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteSubmitFailure implements ObserverInterface
{
    /**
     * @var \Magento\GiftCardAccount\Helper\Data
     */
    protected $giftCAHelper;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     */
    protected $giftCardAccountFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * QuoteSubmitFailure constructor.
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param Data $helper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     */
    public function __construct(
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
    ) {
        $this->giftCAHelper = $giftCAHelper;
        $this->helper = $helper;
        $this->giftCardAccountFactory = $giftcardaccountFactory;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        if (!$this->helper->isActive()) {
            return;
        }

        $exception = $observer->getEvent()->getException();
        if ($exception instanceof BlockOrderPlaceException) {
            return;
        }

        $order = $observer->getEvent()->getOrder();
        $cards = $this->giftCAHelper->getCards($order);
        if (empty($cards)) {
            return;
        }

        $abstractGiftCardEntities = $order->getData('abstract_gift_card_entities');

        foreach ($cards as $giftCard) {
            if (!isset($abstractGiftCardEntities[$giftCard['c']])) {
                try {
                    $giftCardAccount = $this->giftCardAccountFactory->create()->loadByCode($giftCard['c']);
                    $entity = $this->abstractGiftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            } else {
                $entity = $abstractGiftCardEntities[$giftCard['c']]['entity'];
            }
            $service = $entity->getService();
            $service->setOrder($order);
            $service->setStore($order->getStoreId());
            $entity->setOrder($order);
            $service->validate()->cancel($exception->getMessage(), $entity->getToken(), $entity->getAmount());
        }
    }
}
