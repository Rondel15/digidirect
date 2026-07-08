<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Action\Rate;

class PerItem extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        $itemsCount = count($this->validItems);

        $amountValue = $this->getAmountValue();
        $resultAmountValue = $amountValue * $itemsCount;
        $this->_setAmountValue($resultAmountValue);

        return $this;
    }

    /**
     * Calculate percent of amount
     *
     * @return AbstractRate
     */
    protected function percent()
    {
        $amount = 0;
        $amountValue = $this->getAmountValue() ? $this->getAmountValue() / 100 : 0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->validItems as $item) {
            $amount += $item->getPrice() * $amountValue;
        }

        $this->_setAmountValue($amount);

        return $this;
    }
}
