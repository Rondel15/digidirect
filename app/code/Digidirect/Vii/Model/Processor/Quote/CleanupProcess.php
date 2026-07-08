<?php

namespace Digidirect\Vii\Model\Processor\Quote;

use Digidirect\AI\Model\Engine\Processor\ProcessorInterface;
use Magento\Quote\Api\Data\CartInterface;
use Digidirect\Vii\Service\Config\Config;

/**
 * Class CleanupProcess
 * @package Digidirect\Vii\Model\Processor\Quote
 */
class CleanupProcess extends \Digidirect\Vii\Model\Processor\ProcessAbstract implements ProcessorInterface
{
    const PROCESS_CODE = 'digidirect_vii_abandoned_cart_cleanup';

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * CleanupProcess constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param Config $config
     * @param null $initParams
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        Config $config,
        $initParams = null
    ) {
        parent::__construct(
            $giftCAHelper,
            $giftcardaccountFactory,
            $abstractGiftCardEntityRepository,
            $viiGiftCardEntityRepository,
            $initParams
        );
        $this->quoteRepository = $quoteRepository;
        $this->config = $config;
    }

    /**
     * @return bool
     */
    public function process()
    {
        $requestParameters = $this->getRunOptions();
        $quote = $requestParameters['quote'] ?? null;

        if (is_numeric($quote)) {
            $quote = $this->quoteRepository->get($quote);
        }

        if (!$quote instanceof CartInterface) {
            throw new \InvalidArgumentException('Invalid request params');
        }

        $cards = $this->giftCAHelper->getCards($quote);
        if (empty($cards)) {
            return true;
        }

        try {
            foreach ($cards as $giftCard) {
                /**
                 * @var \Magento\GiftCardAccount\Model\GiftCardAccount $giftCardAccount
                 */
                $giftCardAccount = $this->giftCardAccountFactory->create()->loadByCode($giftCard['c']);

                $entity = $this->abstractGiftCardEntityRepository->loadByGiftCardAccount($giftCardAccount);
                $entityQuoteData = $this->viiGiftCardEntityRepository->getEntityQuoteData(
                    $entity,
                    $quote->getId()
                );

                if ($entityQuoteData
                    && $this->config->getExpireDate()
                    && $entityQuoteData->getStartDate() > $this->config->getExpireDate()) {
                    continue;
                }

                $giftCardAccount->removeFromCart(false, $quote);

                if ($entityQuoteData && $entityQuoteData->getToken()) {
                    $entity->setToken($entityQuoteData->getToken());
                }
                $service = $entity->getService();
                $service->setQuote($quote);
                $service->setStore($quote->getStoreId());
                $service->validate()->cancel('', $entity->getToken());
            }

            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->throwException($e->getMessage(), $e, 1);
        }
        return true;
    }
}
