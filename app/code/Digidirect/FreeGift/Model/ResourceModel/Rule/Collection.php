<?php

namespace Digidirect\FreeGift\Model\ResourceModel\Rule;

use Digidirect\FreeGift\Model\ResourceModel\Rule as RuleResource;
use Digidirect\FreeGift\Api\Data\RuleInterface;

/**
 * Class Collection
 *
 * @package Digidirect\FreeGift\Model\ResourceModel\Rule
 */
class Collection extends \Magento\SalesRule\Model\ResourceModel\Rule\Collection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\FreeGift\Model\Rule', 'Magento\SalesRule\Model\ResourceModel\Rule');
    }

    /**
     * @return $this
     */
    protected function addEnableOnDpdFilter()
    {
        $this->getSelect()->where(
            $this->getConnection()->quoteInto('freegift.' . RuleInterface::FIELD_ENABLE_ON_PDP . '= ?', 1)
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function addShowDescOnPdp()
    {
        $this->getSelect()->where(
            $this->getConnection()->quoteInto('freegift.' . RuleInterface::FIELD_SHOW_DESC_ON_PDP . '= ?', 1)
        );

        return $this;
    }

    /**
     * @param bool $withEnableOnPdpFilter
     * @param bool $withShowDescOnPdp
     * @return $this|Collection
     */
    public function joinFreeGift($withEnableOnPdpFilter = false, $withShowDescOnPdp = false)
    {
        $this->getSelect()->join(
            ['freegift' => $this->getTable('digidirect_freegift_rule')],
            new \Zend_Db_Expr(
                'main_table.rule_id = freegift.' . RuleInterface::FIELD_SALESRULE_ID
            )
        );

        if ($withEnableOnPdpFilter) {
            $this->addEnableOnDpdFilter();
        }

        if ($withShowDescOnPdp) {
            $this->addShowDescOnPdp();
        }

        return $this;
    }
}
