<?php
namespace Digidirect\CollectAbstractEntity\Plugin\Magento\Quote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\QuoteManagement as Subject;

/**
 * Class QuoteManagement
 *
 * @package Digidirect\CollectAbstractEntity\Plugin\Magento\Quote\Model
 */
class QuoteManagement
{
    /**
     * @var \Digidirect\CollectAbstractEntity\Model\PrefillFields
     */
    protected $prefillFields;

    /**
     * QuoteRepository constructor.
     *
     * @param \Digidirect\CollectAbstractEntity\Model\PrefillFields $prefillFields
     */
    public function __construct(
        \Digidirect\CollectAbstractEntity\Model\PrefillFields $prefillFields
    ) {
        $this->prefillFields = $prefillFields;
    }

    /**
     * @see \Magento\Quote\Model\QuoteManagement::submit
     * @param Subject $subject
     * @param CartInterface&\Magento\Quote\Model\Quote $quote
     * @param array $orderData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(Subject $subject, QuoteEntity $quote, $orderData = [])
    {
        $this->prefillFields->prefill($quote);
    }
}
