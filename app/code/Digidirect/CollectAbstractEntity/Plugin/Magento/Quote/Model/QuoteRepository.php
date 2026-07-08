<?php
namespace Digidirect\CollectAbstractEntity\Plugin\Magento\Quote\Model;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository as Subject;

/**
 * Class QuoteRepository
 *
 * @package Digidirect\CollectAbstractEntity\Plugin\Magento\Quote\Model
 */
class QuoteRepository
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
     * @param \Magento\Quote\Model\QuoteRepository $subject
     * @param CartInterface&\Magento\Quote\Model\Quote $quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(Subject $subject, CartInterface $quote)
    {
        $this->prefillFields->prefill($quote);
    }
}
