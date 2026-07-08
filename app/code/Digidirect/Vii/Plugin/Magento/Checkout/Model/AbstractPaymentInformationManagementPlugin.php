<?php

namespace Digidirect\Vii\Plugin\Magento\Checkout\Model;

use Digidirect\Vii\Model\ServiceTransactionManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Message\ManagerInterface as ManagerInterface;
use Digidirect\Vii\Service\Config\Config;
use Magento\Store\Model\StoreManagerInterface;

class AbstractPaymentInformationManagementPlugin
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var ServiceTransactionManagement
     */
    protected $serviceTransactionManagement;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * AbstractPaymentInformationManagementPlugin constructor.
     * @param CartRepositoryInterface $cartRepository
     * @param ServiceTransactionManagement $serviceTransactionManagement
     * @param Config $config
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ServiceTransactionManagement $serviceTransactionManagement,
        Config $config,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager
    ) {
        $this->cartRepository = $cartRepository;
        $this->serviceTransactionManagement = $serviceTransactionManagement;
        $this->config = $config;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $errorMessage
     * @param bool $isProcessQueued
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function setErrorMessage($errorMessage, $isProcessQueued)
    {
        if ($isProcessQueued) {
            $storeId = $this->storeManager->getStore()->getId();
            $mapping = $this->config->getNotRespondingMessageMapping($storeId);
            $errorMessage = isset($mapping['Undo'])
                ? $mapping['Undo']
                : Config::NOT_RESPONDING_DEFAULT_MESSAGE;
        }
        $this->messageManager->addErrorMessage($errorMessage);
    }
}
