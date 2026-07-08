<?php

namespace Digidirect\AbstractGiftCard\Observer;

use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Magento\Sales\Model\Order;

class QuoteSubmitSuccess implements ObserverInterface
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
     * QuoteSubmitSuccess constructor.
     *
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
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

        $order = $observer->getEvent()->getOrder();
        $cards = $this->giftCAHelper->getCards($order);
        if (empty($cards)) {
            return;
        }

        $abstractGiftCardEntities = $order->getData('abstract_gift_card_entities');

        foreach ($cards as $giftCard) {
            try {
                if (isset($abstractGiftCardEntities[$giftCard[Giftcardaccount::CODE]])) {
                    $entity = $abstractGiftCardEntities[$giftCard[Giftcardaccount::CODE]]['entity'];
                } else {
                    $giftCardAccount = $this->giftCardAccountFactory->create()
                        ->loadByCode($giftCard[Giftcardaccount::CODE]);

                    $entity = $this->abstractGiftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
                }

                if ($giftCard[Giftcardaccount::CODE] != $entity->getCode()) {
                    continue;
                }

                $amount = $giftCard[Giftcardaccount::AUTHORIZED];
                $entityOrderData = new \Magento\Framework\DataObject(['status' => $entity->getStatus()]);
                if ($this->isAcceptAvailable($order, $entity, $giftCard)) {
                    $service = $entity->getService();
                    $service->setStore($order->getStoreId());
                    $service->setOrder($order);
                    $service->validate()->accept($amount, $entity->getToken());
                    $entityOrderData->setStatus(AbstractGiftCardEntity::STATUS_ACCEPT);
                }

                $entityOrderData->setOrderId($order->getId());
                $entityOrderData->setAmount($amount);
                $entityOrderData->setToken($entity->getToken());
                $entityOrderData->setAbstractGiftCardEntityId($entity->getEntityId());
                $this->abstractGiftCardEntityRepository->saveEntityOrderData($entityOrderData);
            } catch (NoSuchEntityException $e) {
                continue;
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $abstractGiftCard
     * @param array $giftCard
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAcceptAvailable($order, $abstractGiftCard, $giftCard)
    {
        if ($this->helper->isAcceptOnlyForPaidOrders()) {
            $result = $order->getBaseTotalDue() <= 0;
        } else {
            $result = $order->getBaseTotalDue() <= 0
                || $order->getState() == Order::STATE_PROCESSING
                || $order->getState() == Order::STATE_COMPLETE;
        }

        return $result;
    }
}
