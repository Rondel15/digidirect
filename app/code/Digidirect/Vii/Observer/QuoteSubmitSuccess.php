<?php

namespace Digidirect\Vii\Observer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GiftCardAccount\Model\Giftcardaccount;
use Digidirect\AbstractGiftCard\Model\AbstractGiftCardEntity;
use Magento\Framework\Event\Observer; // Ensure this is also used
/**
 * Class QuoteSubmitSuccess
 * @package Digidirect\Vii\Observer
 */
class QuoteSubmitSuccess extends \Digidirect\AbstractGiftCard\Observer\QuoteSubmitSuccess
{
    /**
     * @var \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $viiGiftCardEntityRepository;

    /**
     * @var \Digidirect\Vii\Service\Config\Config
     */
    protected $config;

    protected $logger;

    /**
     * QuoteSubmitSuccess constructor.
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param \Digidirect\Vii\Service\Config\Config $config
     */
    public function __construct(
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Digidirect\AbstractGiftCard\Helper\Data $helper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        \Digidirect\Vii\Service\Config\Config $config
        ,\Psr\Log\LoggerInterface $logger
    ) {
        $this->viiGiftCardEntityRepository = $viiGiftCardEntityRepository;
        $this->config = $config;
        $this->logger = $logger;
        parent::__construct($giftCAHelper, $helper, $giftcardaccountFactory, $abstractGiftCardEntityRepository);

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Sales\Model\Order $order
         */
        $this->logger->info('DEBUG_VII_OBSERVER: Start of execute method.');

        $eventName = $observer->getEvent()->getName(); // Get the current event name for logging

        if (!$this->helper->isActive()) {
            $this->logger->info("Vii Not Active");
            return;
        }

        $this->logger->info('VII Observer: helper is active - continuing execution');

        // --- UPDATED ORDER RETRIEVAL LOGIC ---
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getEvent()->getOrder(); // Attempt to get it the common way

        // If getOrder() returns null, try getData('order') which is often explicitly set
        if (!$order) {
            $order = $observer->getEvent()->getData('order');
            if ($order) {
                $this->logger->info('DEBUG_VII_OBSERVER: Successfully retrieved order via getData("order").');
            }
        }

        // Log the order object itself (or its class if not null) BEFORE the ID check
        $this->logger->info('DEBUG_VII_OBSERVER: Order object after retrieval attempt: ' . ($order ? get_class($order) : 'NULL'));
        // --- END UPDATED ORDER RETRIEVAL LOGIC ---

        // --- TEMPORARY CHANGE: REMOVE !$$order->getId() check ---
        if (!$order) { // ONLY CHECK IF $order IS NULL. We will inspect ID in the full data dump.
            $this->logger->info('DEBUG_VII_OBSERVER: Order object is NULL. Returning. (Final Check)');
            return;
        }
        // --- END TEMPORARY CHANGE ---

        $this->logger->info('DEBUG_VII_OBSERVER: Processing Order ID: ' . $order->getId());



        //$order = $observer->getEvent()->getOrder();
        //$cards = $this->giftCAHelper->getCards($order);

        // --- Log around your gift card helper calls ---
        $this->logger->info('DEBUG_VII_OBSERVER: Before calling $this->giftCAHelper->getCards().');
        $cards = $this->giftCAHelper->getCards($order); // THIS IS A CRITICAL LINE
        $this->logger->info('DEBUG_VII_OBSERVER: After calling $this->giftCAHelper->getCards(). Cards found: ' . count($cards));


        $this->logger->info('DEBUG_VII_OBSERVER: After manual retrieval attempt. Cards now: ' . count($cards));
        if (empty($cards)) {
            $this->logger->info('DEBUG_VII_OBSERVER: No gift cards found to process. Returning.');
            return;
        }

        foreach ($cards as $giftCard) {
            $this->logger->info('DEBUG_VII_OBSERVER: Inside loop for gift card: ' . ($giftCard[\Magento\GiftCardAccount\Model\Giftcardaccount::CODE] ?? 'N/A'));
            try {

                $giftCardCode = $giftCard[Giftcardaccount::CODE] ?? null;
                if (!$giftCardCode) {
                    $this->logger->warning('Vii observer: Gift card code is null, skipping this item.');
                    continue;
                }

                $this->logger->info("Processing Gift Card: {$giftCard[Giftcardaccount::CODE]}");

                $giftCardAccount = $this->giftCardAccountFactory->create()
                    ->loadByCode($giftCard[Giftcardaccount::CODE]);

                if (!$giftCardAccount->getId()) {
                    $this->logger->warning('Vii observer: Gift card account not found for code: ' . $giftCardCode);
                    continue;
                }

                $entity = $this->abstractGiftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
                $this->logger->info('DEBUG_VII_OBSERVER: Gift card entity loaded successfully.');

                if (!$entity->getEntityId()) {
                    $this->logger->warning('Vii observer: Abstract Gift Card Entity not found for gift card account ID: ' . $giftCardAccount->getId());
                    continue;
                }

                $entityQuoteData = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                    $entity,
                    $order->getQuoteId()
                );
                if ($entityQuoteData && $entityQuoteData->getToken()) {
                    $entity->setToken($entityQuoteData->getToken());
                    $entity->setStatus(AbstractGiftCardEntity::STATUS_HOLD);
                }

                if ($giftCard[Giftcardaccount::CODE] != $entity->getCode()) {
                    continue;
                }

                $amount = $giftCard[Giftcardaccount::AUTHORIZED];
                $entityOrderData = new \Magento\Framework\DataObject(['status' => $entity->getStatus()]);


                if ($this->isAcceptAvailable($order, $entity, $giftCard)) {
                    $service = $entity->getService();
                    $this->logger->info('DEBUG_VII_OBSERVER: Gift card service retrieved. Before setting store/order on service.');
                    $service->setStore($order->getStoreId());
                    $service->setOrder($order);
                    $this->logger->info('DEBUG_VII_OBSERVER: After setting store/order on service. Before validate/accept.');
                    $service->validate()->accept($amount, $entity->getToken());
                    $entityOrderData->setStatus(AbstractGiftCardEntity::STATUS_ACCEPT);
                    $entityOrderData->setOrderId($order->getId());
                    $entityOrderData->setAmount($amount);
                    $entityOrderData->setToken($entity->getToken());
                    $entityOrderData->setAbstractGiftCardEntityId($entity->getEntityId());
                    $this->abstractGiftCardEntityRepository->saveEntityOrderData($entityOrderData);
                }

            } catch (NoSuchEntityException $e) {
                $this->logger->error('Vii observer: NoSuchEntityException for gift card: ' . ($giftCard[Giftcardaccount::CODE] ?? 'N/A') . ' - ' . $e->getMessage());
                continue;
            }

        }
    }
}
