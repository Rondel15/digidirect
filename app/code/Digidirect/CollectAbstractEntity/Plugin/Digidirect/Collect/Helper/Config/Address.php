<?php
namespace Digidirect\CollectAbstractEntity\Plugin\Digidirect\Collect\Helper\Config;

use Digidirect\Collect\Helper\Config\Address as Subject;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Address
 *
 * @package Digidirect\CollectAbstractEntity\Plugin\Digidirect\Collect\Helper\Config
 */
class Address
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
     * @param Subject $subject
     * @param \Closure $closure
     * @param \Magento\Quote\Model\Quote\Address $address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return \Magento\Quote\Model\Quote\Address
     * @see \Digidirect\Collect\Helper\Config\Address::applyDummyAddress
     */
    public function aroundApplyDummyAddress(
        Subject $subject,
        \Closure $closure,
        \Magento\Quote\Model\Quote\Address $address
    ) {
        if ($this->prefillFields->prefill($address->getQuote())) {
            return $address;
        }

        return $closure($address);
    }
}
