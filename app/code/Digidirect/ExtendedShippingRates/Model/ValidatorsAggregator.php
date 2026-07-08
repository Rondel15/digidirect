<?php

namespace Digidirect\ExtendedShippingRates\Model;

use Digidirect\ExtendedShippingRates\Model\ValidatorFactory;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;

/**
 * Class ValidatorsAggregator
 * @package Digidirect\ExtendedShippingRates\Model
 */
class ValidatorsAggregator
{
    /** @var Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /**
     * @var \Magento\Quote\Model\Quote|null
     */
    protected $currentProcessedQuote;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * ValidatorsAggregator constructor.
     * @param \Digidirect\ExtendedShippingRates\Model\ValidatorFactory $validatorFactory
     * @param Session $checkoutSession
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     */
    public function __construct(
        ValidatorFactory $validatorFactory,
        Session $checkoutSession,
        \Magento\Framework\App\State $state,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession
    ) {
        $this->validatorFactory = $validatorFactory;
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
     * @return \Digidirect\ExtendedShippingRates\Model\Validator
     */
    public function getValidatorByCurrentProcessedQuote()
    {
        $quote = $this->getCurrentProcessedQuote();
        return $this->createValidator($quote);
    }

    /**
     * @param Quote $quote
     * @return \Digidirect\ExtendedShippingRates\Model\Validator
     */
    public function createValidator($quote)
    {
        if (empty($this->validators[$quote->getId()])) {
            $this->validators[$quote->getId()] = $this->validatorFactory->create()->setQuote($quote);
        }

        return $this->validators[$quote->getId()];
    }

    /**
     * @return $this
     */
    public function resetCurrentProcessedQuote()
    {
        $this->setCurrentProcessedQuote($this->session->getQuote());
        return $this;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function resetCacheForQuote($quote)
    {
        $this->validators[$quote->getId()] = null;
        return $this;
    }
}
