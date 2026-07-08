<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Action\Rate;

use InvalidArgumentException;
use Magento\Checkout\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Digidirect\ExtendedShippingRates\Model\Rule;
use Digidirect\ExtendedShippingRates\Model\Validator;
use Digidirect\ExtendedShippingRates\Api\Data\AbstractRateInterface;

abstract class AbstractRate implements AbstractRateInterface
{

    /** @var Validator */
    protected $validator;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /** @var Quote */
    protected $quote;

    /** @var Method */
    protected $rate;

    /** @var string */
    protected $calculationMethod;

    /** @var string */
    protected $applyMethod;

    /** @var string */
    protected $amountType;

    /** @var float */
    protected $amountValue;

    /** @var array */
    protected $validItems = [];

    /**
     * AbstractRate constructor.
     * @param Validator $validator
     * @param PriceCurrencyInterface $priceCurrency
     * @param Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Framework\App\State $state
     * @param string $amountType
     * @param string $calculationMethod
     * @param string $applyMethod
     */
    public function __construct(
        Validator $validator,
        PriceCurrencyInterface $priceCurrency,
        Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Framework\App\State $state,
        $amountType,
        $calculationMethod,
        $applyMethod
    ) {
        $this->validator = $validator;
        $this->priceCurrency = $priceCurrency;
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
        }
        $this->setAmountType($amountType);
        $this->setCalculationMethod($calculationMethod);
        $this->setApplyMethod($applyMethod);
    }

    /**
     * Do calculations based on the current calculation method
     *
     * @return AbstractRate
     */
    protected function doCalculation()
    {
        $this->{$this->calculationMethod}();
        return $this;
    }

    /**
     * Apply calculation result to current rate
     *
     * @return AbstractRate
     */
    protected function apply()
    {
        $this->{$this->applyMethod}();
        return $this;
    }

    /**
     * @param Rule $rule
     * @param Method $rate
     * @param Quote $quote
     * @return Method
     */
    public function calculate(Rule $rule, Method $rate, Quote $quote)
    {
        $this->setQuote($quote);
        $this->setRate($rate);
        $this->prepareValidItems($rule);

        $amount = $rule->getAmount();
        $amountType = $this->getAmountType();
        if (!isset($amount[$amountType]['value'])) {
            return $this->getRate();
        }

        $this->_setAmountValue($amount[$amountType]['value']);
        $this->doCalculation();
        $this->apply();

        return $this->getRate();
    }

    /**
     * Fill the "valid-items" array with items
     *
     * @param Rule $rule
     * @return $this
     */
    protected function prepareValidItems(Rule $rule)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->getQuote()->getAllItems() as $item) {
            if (!$item->getChildren() && $this->validator->isValidItem($rule, $item)) {
                $this->validItems[$item->getId()] = $item;
            }
        }

        return $this;
    }

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    abstract protected function fixed();

    /**
     * Calculate percent of amount
     *
     * @return AbstractRate
     */
    abstract protected function percent();

    /**
     * Overwrite shipping method amount
     *
     * @return AbstractRate
     */
    protected function overwrite()
    {
        $rate = $this->getRate();
        $amountValue = $this->getAmountValue();
        $price = $this->convertPrice($amountValue);
        $rate->setPrice($price);

        return $this;
    }

    /**
     * Add surcharge to the amount of the shipping method
     *
     * @return AbstractRate
     */
    protected function surcharge()
    {
        $rate = $this->getRate();
        $amountValue = $this->getAmountValue();
        $price = $this->convertPrice($amountValue);
        $finalPrice = $rate->getPrice() + $price;
        $rate->setPrice($finalPrice);

        return $this;
    }

    /**
     * Reduces the amount of the shipping method by the discount sum
     *
     * @return AbstractRate
     */
    protected function discount()
    {
        $rate = $this->getRate();
        $amountValue = $this->getAmountValue();
        $price = $this->convertPrice($amountValue);
        $priceWithDiscount = $rate->getPrice() - $price;
        $finalPrice = $priceWithDiscount > 0 ? $priceWithDiscount : 0;
        $rate->setPrice($finalPrice);

        return $this;
    }

    /**
     * Convert base price value to store price value
     *
     * @param string $amountValue
     * @return float
     */
    protected function convertPrice($amountValue)
    {
        $price = $this->priceCurrency->convert($amountValue, $this->quote->getStore());
        return $price;
    }

    /**
     * Set current amount value
     *
     * @param string $value
     * @return AbstractRate
     */
    protected function _setAmountValue($value)
    {
        $this->amountValue = floatval($value);
        return $this;
    }

    /**
     * Returns current amount value
     *
     * @return float
     */
    public function getAmountValue()
    {
        return $this->amountValue;
    }

    /**
     * Set method of calculation: fixed or percent
     *
     * @param string $method
     * @return AbstractRateInterface
     */
    public function setCalculationMethod($method)
    {
        $availableCalculationMethods = Rule::getActionCalculations();

        if (!in_array($method, $availableCalculationMethods)) {
            throw new InvalidArgumentException('Calculation method ' . $method . ' is not available');
        }

        $this->calculationMethod = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getCalculationMethod()
    {
        return $this->calculationMethod;
    }

    /**
     * Set apply method: overwrite, surcharge or discount
     *
     * @param string $method
     * @return AbstractRateInterface
     */
    public function setApplyMethod($method)
    {
        $availableApplyMethods = Rule::getActionMethods();

        if (!in_array($method, $availableApplyMethods)) {
            throw new InvalidArgumentException('Apply method ' . $method . ' is not available');
        }

        $this->applyMethod = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getApplyMethod()
    {
        return $this->applyMethod;
    }

    /**
     * Set full amount type.
     * Usually this type is used to find the amount method.
     *
     * @param string $type
     * @return AbstractRateInterface
     */
    public function setAmountType($type)
    {
        $this->amountType = $type;
        return $this;
    }

    /**
     * Get full amount type.
     * Usually this type is used to find the amount method.
     *
     * @return string
     */
    public function getAmountType()
    {
        return $this->amountType;
    }

    /**
     * @param Quote $quote
     * @return $this
     */
    public function setQuote(Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return Quote
     */
    public function getQuote()
    {
        if (!$this->quote) {
            $this->setQuote($this->session->getQuote());
        }

        return $this->quote;
    }

    /**
     * Set current shipping method rate for modifications
     *
     * @param Method $rate
     * @return $this|mixed
     */
    public function setRate(Method $rate)
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * Get current active shipping method rate
     *
     * @return Method
     */
    public function getRate()
    {
        return $this->rate;
    }
}
