<?php
namespace Digidirect\ExtendedShippingRates\Model\Rule\Action\Rate;

class PerProduct extends AbstractRate
{

    /**
     * Calculate fixed amount
     *
     * @return AbstractRate
     */
    protected function fixed()
    {
        $productQty = 0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->validItems as $item) {
            if ($item->getParentItem()) {
                $qty = $item->getParentItem()->getQty();
            } else {
                $qty = $item->getQty();
            }
            $productQty += $qty;
        }

        $amountValue = $this->getAmountValue();
        $resultAmountValue = $amountValue * $productQty;
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
        $amountValue = $this->getAmountValue() ? $this->getAmountValue() / 100 : 0;
        $price = 0;
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($this->validItems as $item) {
            $price += $item->getRowTotal();
        }
        $amount = $price * $amountValue;

        $this->_setAmountValue($amount);

        return $this;
    }
}
