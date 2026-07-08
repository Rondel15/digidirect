<?php

namespace Digidirect\Vii\Plugin\Magento\Framework\DB;

use Digidirect\Vii\Model\ServiceTransactionManagement;
use Digidirect\Vii\Service\Exeption\ServiceExeption;
use Digidirect\Vii\Service\Config\Config;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class TransactionPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ServiceTransactionManagement
     */
    protected $serviceTransactionManagement;

    /**
     * @var Config
     */
    protected $config;

    /**
     * TransactionPlugin constructor.
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param ServiceTransactionManagement $serviceTransactionManagement
     * @param Config $config
     */
    public function __construct(
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        ServiceTransactionManagement $serviceTransactionManagement,
        Config $config
    ) {
        $this->request = $request;
        $this->orderRepository = $orderRepository;
        $this->serviceTransactionManagement = $serviceTransactionManagement;
        $this->config = $config;
    }

    /**
     * @param Transaction $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSave(
        Transaction $subject,
        \Closure $proceed
    ) {
        try {
            $result = $proceed();
        } catch (\Exception $exception) {
            $previousException = $exception->getPrevious();
            $orderId = $this->request->getParam('order_id');

            if ($orderId && $previousException instanceof ServiceExeption) {
                $order = $this->orderRepository->get($orderId);
                if ($this->serviceTransactionManagement->reversePreviousTransaction($order->getQuoteId(), $order)) {
                    if ($this->serviceTransactionManagement->isProcessQueued()) {
                        $storeId = $order->getStoreId();
                        $mapping = $this->config->getNotRespondingMessageMapping($storeId);
                        $errorMessage = isset($mapping['Undo'])
                            ? $mapping['Undo']
                            : Config::NOT_RESPONDING_DEFAULT_MESSAGE;
                        throw new LocalizedException(__('Giftcard acceptance failed: %1', $errorMessage, $exception));
                    }
                }
            }
            throw $exception;
        }
        return $result;
    }
}
