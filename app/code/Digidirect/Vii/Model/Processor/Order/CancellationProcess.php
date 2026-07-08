<?php

namespace Digidirect\Vii\Model\Processor\Order;

use Digidirect\AI\Model\Engine\Processor\ProcessorInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class CancellationProcess
 * @package Digidirect\Vii\Model\Processor\Order
 */
class CancellationProcess extends \Digidirect\Vii\Model\Processor\ProcessAbstract implements ProcessorInterface
{
    const PROCESS_CODE = 'digidirect_vii_not_paid_order_cancel';

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    protected $orderManagement;

    /**
     * CancellationProcess constructor.
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param null $initParams
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement,
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        $initParams = null
    ) {
        parent::__construct(
            $giftCAHelper,
            $giftcardaccountFactory,
            $abstractGiftCardEntityRepository,
            $viiGiftCardEntityRepository,
            $initParams
        );
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
    }

    /**
     * @return bool
     */
    public function process()
    {
        $requestParameters = $this->getRunOptions();
        $order = $requestParameters['order'] ?? null;

        if (is_numeric($order)) {
            $order = $this->orderRepository->get($order);
        }

        if (!$order instanceof OrderInterface) {
            throw new \InvalidArgumentException('Invalid request params');
        }

        $cards = $this->giftCAHelper->getCards($order);
        if (empty($cards)) {
            return true;
        }

        try {
            foreach ($cards as $giftCard) {
                $giftCardAccount = $this->giftCardAccountFactory->create()->loadByCode($giftCard['c']);
                $entity = $this->abstractGiftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
                $entityQuoteData = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                    $entity,
                    $order->getQuoteId()
                );
                if ($entityQuoteData && $entityQuoteData->getToken()) {
                    $entity->setToken($entityQuoteData->getToken());
                }
                $service = $entity->getService();
                $service->setOrder($order);
                $service->setStore($order->getStoreId());
                $service->validate()->cancel('', $entity->getToken());
            }

            $this->orderManagement->cancel((int)$order->getId());
        } catch (\Exception $e) {
            $this->throwException($e->getMessage(), $e, 1);
        }
        return true;
    }
}
