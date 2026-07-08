<?php
namespace Digidirect\ProductOverlay\Model\ResourceModel\Rule;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class CatalogRule
 * @package Digidirect\ProductOverlay\Model\ResourceModel\Rule
 */
class CatalogRule extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalogrule_product', 'rule_product_id');
    }

    /**
     * @param array $ruleIds
     * @return []
     */
    public function getPriceRuleProductsByRule(array $ruleIds = [])
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), ['rule_id', 'product_id'])
            ->where($this->getConnection()->quoteInto('rule_id IN (?)', $ruleIds));
        $all = $this->getConnection()->fetchAll($select);

        $result = [];
        if (!empty($all)) {
            foreach ($all as $key => $selectResult) {
                $result[$selectResult['product_id']][] = $selectResult['rule_id'];
            }
        }

        return $result;
    }
}
