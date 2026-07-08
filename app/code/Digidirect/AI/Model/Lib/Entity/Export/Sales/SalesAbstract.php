<?php

namespace Digidirect\AI\Model\Lib\Entity\Export\Sales;

use Digidirect\AI\Model\Lib\Entity\Export\ExportAbstract;

abstract class SalesAbstract extends ExportAbstract
{
    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrder($type = 'join', $cond = 'main_table.order_id = {sales_order}.entity_id')
    {
        $this->joinOnce($type, 'sales_order', $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinPayment($type = 'join', $cond = '{sales_order}.entity_id = {sales_order_payment}.parent_id')
    {
        $this->joinOrder();
        $this->joinOnce($type, 'sales_order_payment', $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinBillingAddress(
        $type = 'join',
        $cond = '{sales_order}.billing_address_id = billing_address.entity_id'
    ) {
        $this->joinOrder();
        $this->joinOnce($type, ['billing_address' => 'sales_order_address'], $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinShippingAddress(
        $type = 'join',
        $cond = '{sales_order}.shipping_address_id = shipping_address.entity_id'
    ) {
        $this->joinOrder();
        $this->joinOnce($type, ['shipping_address' => 'sales_order_address'], $cond);

        return $this;
    }
}
