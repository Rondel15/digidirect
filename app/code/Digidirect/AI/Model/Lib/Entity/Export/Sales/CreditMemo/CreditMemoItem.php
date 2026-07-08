<?php
namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\CreditMemo;

use Digidirect\AI\Model\Lib\Entity\Export\Sales\CreditMemo\CreditMemoItemInterface;
use Digidirect\AI\Model\Lib\Entity\Export\Sales\SalesAbstract;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class CreditMemoItem extends SalesAbstract implements CreditMemoItemInterface
{
    /**
     * CreditMemoItem constructor.
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
        $this->select = $this->select->from(['main_table' => 'sales_creditmemo_item'], $cols)->distinct();
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinCreditMemo(
        $type = 'join',
        $cond = 'main_table.parent_id = {sales_creditmemo}.entity_id'
    ) {
        $this->joinOnce($type, 'sales_creditmemo', $cond);
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
     * Get name of entity
     *
     * @return string
     */
    public function getName()
    {
        return 'CreditMemo';
    }
}
