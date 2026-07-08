<?php
namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\CreditMemo;

use Digidirect\AI\Model\Lib\Entity\Export\Sales\SalesAbstract;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

class CreditMemo extends SalesAbstract implements CreditMemoInterface
{
    /**
     * CreditMemo constructor.
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
        $this->select = $this->select->from(['main_table' => 'sales_creditmemo'], $cols);
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinCreditMemoItems(
        $type = 'join',
        $cond = 'main_table.entity_id = {sales_creditmemo_item}.parent_id'
    ) {
        $this->joinOnce($type, 'sales_creditmemo_item', $cond);
        return $this;
    }

    /**
     * @param string $type
     * @param string $cond
     * @return $this
     */
    public function joinOrderItems(
        $type = 'join',
        $cond = '{sales_creditmemo_item}.order_item_id = {sales_order_item}.item_id'
    ) {
        $this->joinCreditMemoItems();
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
        $cond = '{sales_creditmemo_item}.product_id = {catalog_product_entity}.entity_id'
    ) {
        $this->joinCreditMemoItems();
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
