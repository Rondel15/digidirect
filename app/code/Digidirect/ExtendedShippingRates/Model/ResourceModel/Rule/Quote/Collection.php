<?php
namespace Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\Quote;

class Collection extends \Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule\Collection
{
    /**
     * Add stores for load
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->addStoresToResult();
        return $this;
    }

    /**
     * Init flag for adding rule store ids to collection result
     *
     * @param bool|null $flag
     * @return $this
     */
    public function addStoresToResult($flag = null)
    {
        $flag = $flag ?? true;
        $this->setFlag('add_stores_to_result', $flag);
        return $this;
    }

    /**
     * Add store ids to rules data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('add_stores_to_result') && $this->_items) {
            /** @var \Magento\Rule\Model\AbstractModel $item */
            foreach ($this->_items as $item) {
                $item->afterLoad();
            }
        }

        return $this;
    }

    /**
     * Provide support for store id filter
     *
     * @param string $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'store_ids') {
            return $this->addStoreFilter($condition);
        }

        parent::addFieldToFilter($field, $condition);
        return $this;
    }
}
