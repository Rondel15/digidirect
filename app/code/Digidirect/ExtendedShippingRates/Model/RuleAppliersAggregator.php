<?php

namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Model\RulesApplierFactory;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;

/**
 * Class RuleAppliersAggregator
 * @package Digidirect\ExtendedShippingRates\Model
 */
class RuleAppliersAggregator
{
    /** @var Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $currentProcessedQuote;

    /**
     * @var RulesApplierFactory
     */
    protected $rulesAppliersFactory;

    /**
     * @var array
     */
    protected $rulesAppliers = [];

    /**
     * RuleAppliersAggregator constructor.
     * @param \Digidirect\ExtendedShippingRates\Model\RulesApplierFactory $rulesApplierFactory
     * @param Session $checkoutSession
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     */
    public function __construct(
        RulesApplierFactory $rulesApplierFactory,
        Session $checkoutSession,
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession
    ) {
        $this->rulesAppliersFactory = $rulesApplierFactory;
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
        }
    }

    /**
     * @param Quote $quote
     * @return void
     */
    public function setCurrentProcessedQuote($quote)
    {
        if ($quote instanceof Quote) {
            $this->currentProcessedQuote = $quote;
        }
    }

    /**
     * @return Quote
     */
    public function getCurrentProcessedQuote()
    {
        if (!$this->currentProcessedQuote) {
            $this->currentProcessedQuote = $this->session->getQuote();
        }
        return $this->currentProcessedQuote;
    }

    /**
     * @return \Digidirect\ExtendedShippingRates\Model\RulesApplier
     */
    public function getRulesApplierByCurrentProcessedQuote()
    {
        $quote = $this->getCurrentProcessedQuote();
        return $this->createRulesApplier($quote);
    }

    /**
     * @param Quote $quote
     * @return \Digidirect\ExtendedShippingRates\Model\RulesApplier
     */
    public function createRulesApplier($quote)
    {
        if (empty($this->rulesAppliers[$quote->getId()])) {
            $this->rulesAppliers[$quote->getId()] = $this->rulesAppliersFactory->create()->setQuote($quote);
        }

        return $this->rulesAppliers[$quote->getId()];
    }

    /**
     * @return $this
     */
    public function resetCurrentProcessedQuote()
    {
        $this->setCurrentProcessedQuote($this->session->getQuote());
        return $this;
    }
}
