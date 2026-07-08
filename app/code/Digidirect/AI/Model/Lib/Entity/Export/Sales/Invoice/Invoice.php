<?php
namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\Invoice;

use Digidirect\AI\Model\Lib\Entity\Export\Sales\SalesAbstract;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class Invoice extends SalesAbstract implements InvoiceInterface
{
    /**
     * Invoice constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param string $cols
     * @param array $aliases
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        $cols = '*',
        $aliases = []
    ) {
        parent::__construct($resourceConnection, $aliases);
        $this->select = $this->select->from(['main_table' => 'sales_invoice'], $cols);
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinInvoiceItems($type = 'join', $cond = 'main_table.entity_id = {sales_invoice_item}.parent_id')
    {
        $this->joinOnce($type, 'sales_invoice_item', $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrderItems(
        $type = 'join',
        $cond = '{sales_invoice_item}.order_item_id = {sales_order_item}.item_id'
    ) {
        $this->joinInvoiceItems();
        $this->joinOnce($type, 'sales_order_item', $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinProducts(
        $type = 'join',
        $cond = '{sales_invoice_item}.product_id = {catalog_product_entity}.entity_id'
    ) {
        $this->joinInvoiceItems();
        $this->joinOnce($type, 'catalog_product_entity', $cond);

        return $this;
    }

    /**
     * Get name of entity
     *
     * @return string
     */
    public function getName()
    {
        return 'Invoice';
    }
}
