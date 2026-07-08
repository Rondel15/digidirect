<?php

namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\Invoice;

use Digidirect\AI\Model\Lib\Entity\Export\ExportInterface;

interface InvoiceInterface extends ExportInterface
{
    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrder($type = '', $cond = '');

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrderItems($type = '', $cond = '');

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinPayment($type = '', $cond = '');

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinProducts($type = '', $cond = '');

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinInvoiceItems($type = '', $cond = '');

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinBillingAddress($type = '', $cond = '');

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinShippingAddress($type = '', $cond = '');
}
