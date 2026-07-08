<?php

namespace Digidirect\FreeGift\Model\ResourceModel;

use Digidirect\FreeGift\Api\Data\RuleInterface;

class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_freegift_rule', 'entity_id');
    }

    /**
     * @param array|string $ids
     * @param bool $sortByPriority
     * @param bool $withHidden
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMessagesByRuleIds($ids, $sortByPriority = false, $withHidden = true)
    {
        return $this->_getFreeGiftFieldByRuleIds(
            $ids,
            $sortByPriority,
            $withHidden,
            RuleInterface::FIELD_CART_MESSAGE
        );
    }

    /**
     * @param array|string $ids
     * @param bool $sortByPriority
     * @param bool $withHidden
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPrefixesByRuleIds($ids, $sortByPriority = false, $withHidden = true)
    {
        return $this->_getFreeGiftFieldByRuleIds(
            $ids,
            $sortByPriority,
            $withHidden,
            RuleInterface::FIELD_PREFIX
        );
    }

    /**
     * @param array|string $ids
     * @param bool $sortByPriority
     * @param bool $withHidden
     * @param string $field
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getFreeGiftFieldByRuleIds($ids, $sortByPriority, $withHidden, $field)
    {
        if (!is_array($ids)) {
            $ids = explode(",", $ids);
        }
        $this->getConnection();

        $select = $this->getConnection()->select()
            ->from(
                ['e' => $this->getMainTable()],
                [RuleInterface::FIELD_SALESRULE_ID, $field]
            )->where(RuleInterface::FIELD_SALESRULE_ID . ' IN (?)', $ids);

        if (!$withHidden) {
            $select->where(RuleInterface::FIELD_IS_HIDDEN_FOR_CUSTOMER . '= ?', 0);
        }

        if ($sortByPriority) {
            $select->joinLeft(
                ['salesrule' => $this->getTable('salesrule')],
                'salesrule.rule_id = e.salesrule_id ',
                ['sort_order']
            );
            $select->order('sort_order');
        }

        return $this->getConnection()->fetchAll($select);
    }
}
