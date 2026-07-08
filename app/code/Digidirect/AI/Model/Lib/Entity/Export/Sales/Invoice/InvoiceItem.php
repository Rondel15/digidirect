<?php
namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\Invoice;

use Digidirect\AI\Model\Lib\Entity\Export\Sales\SalesAbstract;
use Digidirect\AI\Model\Lib\Entity\Export\Sales\Invoice\InvoiceItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class InvoiceItem extends SalesAbstract implements InvoiceItemInterface
{
    /**
     * InvoiceItem constructor.
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
        $this->select = $this->select->from(['main_table' => 'sales_invoice_item'], $cols);
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinInvoice($type = 'join', $cond = 'main_table.parent_id = {sales_invoice}.entity_id')
    {
        $this->joinOnce($type, 'sales_invoice', $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrderItems(
        $type = 'join',
        $cond = 'main_table.order_item_id = {sales_order_item}.item_id'
    ) {
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
        $cond = 'main_table.product_id = {catalog_product_entity}.entity_id'
    ) {
        $this->joinOnce($type, 'catalog_product_entity', $cond);

        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrder($type = 'join', $cond = '{sales_order_item}.order_id = {sales_order}.entity_id')
    {
        $this->joinOrderItems();
        $this->joinOnce($type, 'sales_order', $cond);

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
