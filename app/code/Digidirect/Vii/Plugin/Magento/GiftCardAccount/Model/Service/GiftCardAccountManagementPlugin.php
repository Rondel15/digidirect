<?php

namespace Digidirect\Vii\Plugin\Magento\GiftCardAccount\Model\Service;

use Magento\Framework\Exception\NoSuchEntityException;
use Digidirect\Vii\Model\ResourceModel\AbstractGiftCardEntity;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class GiftCardAccountManagementPlugin
 * @package Digidirect\Vii\Plugin\Magento\GiftCardAccount\Model\Service
 */
class GiftCardAccountManagementPlugin
{
    /**
     * @var \Digidirect\Vii\Service\Config\Config
     */
    protected $config;

    /**
     * @var \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $abstractGiftCardEntityRepository;

    /**
     * @var \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface
     */
    protected $viiGiftCardEntityRepository;

    /**
     * @var \Digidirect\AbstractGiftCard\Model\Service\AbstractGiftCardAccountManagement
     */
    protected $management;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * GiftCardAccountManagementPlugin constructor.
     * @param \Digidirect\Vii\Service\Config\Config $config
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param \Digidirect\AbstractGiftCard\Model\Service\AbstractGiftCardAccountManagement $management
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Digidirect\Vii\Service\Config\Config $config,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        \Digidirect\AbstractGiftCard\Model\Service\AbstractGiftCardAccountManagement $management,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->config = $config;
        $this->abstractGiftCardEntityRepository = $abstractGiftCardEntityRepository;
        $this->viiGiftCardEntityRepository = $viiGiftCardEntityRepository;
        $this->management = $management;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param \Magento\GiftCardAccount\Model\Service\GiftCardAccountManagement $subject
     * @param \Closure $proceed
     * @param int $cartId
     * @param string $giftCardCode
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDeleteByQuoteId($subject, \Closure $proceed, $cartId, $giftCardCode)
    {
        if (!$this->config->isActive()) {
            return $proceed($cartId, $giftCardCode);
        }

        try {
            $entity = $this->abstractGiftCardEntityRepository->getGiftCardDataByServiceAndCode(
                $this->config->getServiceCode(),
                $giftCardCode
            );
            $serviceInstance = $this->initService($entity);
            $entityQuoteData = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                $entity,
                $cartId
            );
            if ($entityQuoteData
                && $entityQuoteData->getStatus() != AbstractGiftCardEntity::ENTITY_STATUS_REVERSED
                && $entityQuoteData->getToken()) {
                $entity->setToken($entityQuoteData->getToken());

                $quote = $this->quoteRepository->get($cartId);
                $serviceInstance->setStore($quote->getStoreId());
                $serviceInstance->setQuote($quote);

                if ($serviceInstance->canCancel()) {
                    $serviceInstance->validate()->cancel('', $entity->getToken());
                }
            }
            //@codingStandardsIgnoreStart
        } catch (NoSuchEntityException $e) {
        }
        //@codingStandardsIgnoreEnd

        return $proceed($cartId, $giftCardCode);
    }

    /**
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $abstractGiftCardEntity
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function initService(\Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityInterface $abstractGiftCardEntity)
    {
        if ($abstractGiftCardEntity->getServiceCode()) {
            $serviceInstance = $this->management->getServiceInstanceByCode($abstractGiftCardEntity->getServiceCode());
            $serviceInstance->setAbstractGiftCardEntity($abstractGiftCardEntity);
            return $serviceInstance;
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('Invalid Request Data'));
    }
}
