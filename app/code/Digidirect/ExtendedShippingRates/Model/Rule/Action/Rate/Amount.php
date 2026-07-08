<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Action\Rate;

class Amount extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        return $this;
    }

    /**
     * Calculate percent of amount
     *
     * @return AbstractRate
     */
    protected function percent()
    {
        $rate = $this->getRate();
        $amountValue = $this->getAmountValue() ? $this->getAmountValue() / 100 : 0;
        $amount = $rate->getPrice() * $amountValue;
        $this->_setAmountValue($amount);

        return $this;
    }
}
