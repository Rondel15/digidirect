<?php


namespace Digidirect\Vii\Model\Processor\Quote;

use Digidirect\Vii\Service\Config\Config;

/**
 * Class VisitorCleanupProcess
 * @package Digidirect\Vii\Model\Processor\Quote
 */
class VisitorCleanupProcess extends \Digidirect\Vii\Model\Processor\Quote\CleanupProcess
{
    const PROCESS_CODE = 'digidirect_vii_quote_visitor_cleanup';

    /**
     * @var \Digidirect\Vii\Model\ResourceModel\Customer\Visitor
     */
    protected $visitor;

    /**
     * VisitorCleanupProcess constructor.
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\GiftCardAccount\Helper\Data $giftCAHelper
     * @param \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory
     * @param \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository
     * @param \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository
     * @param Config $config
     * @param \Digidirect\Vii\Model\ResourceModel\Customer\Visitor $visitor
     * @param null $initParams
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\GiftCardAccount\Helper\Data $giftCAHelper,
        \Magento\GiftCardAccount\Model\GiftcardaccountFactory $giftcardaccountFactory,
        \Digidirect\AbstractGiftCard\Api\AbstractGiftCardEntityRepositoryInterface $abstractGiftCardEntityRepository,
        \Digidirect\Vii\Api\AbstractGiftCardEntityRepositoryInterface $viiGiftCardEntityRepository,
        Config $config,
        \Digidirect\Vii\Model\ResourceModel\Customer\Visitor $visitor,
        $initParams = null
    ) {
        parent::__construct(
            $quoteRepository,
            $giftCAHelper,
            $giftcardaccountFactory,
            $abstractGiftCardEntityRepository,
            $viiGiftCardEntityRepository,
            $config,
            $initParams
        );
        $this->visitor = $visitor;
    }

    /**
     * @return bool
     */
    public function process()
    {
        $result = parent::process();
        $requestParameters = $this->getRunOptions();
        $quoteId = $requestParameters['quote'] ?? null;
        if (is_numeric($quoteId)) {
            $this->visitor->deleteVisitorDataByQuoteId($quoteId);
        }
        return $result;
    }
}
