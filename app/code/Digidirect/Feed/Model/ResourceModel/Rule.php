<?php

namespace Digidirect\Feed\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Rule extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_feed_rule', 'rule_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveFeedIds($object);
        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function _beforeDelete(AbstractModel $object)
    {
        if ($feedIds = $this->getFeedIds($object)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Could not remove filter. %1 Feed(s) use(s) this one.', count($feedIds))
            );
        }
        return parent::_beforeDelete($object);
    }

    /**
     * Load feed IDS by rule
     *
     * @param AbstractModel $object
     * @return array
     */
    public function getFeedIds(AbstractModel $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('digidirect_feed_rule_feed'), ['feed_id'])
            ->where('rule_id = ?', $object->getId());

        return $connection->fetchCol($select);
    }

    /**
     * Save feed ids for rule
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function saveFeedIds(AbstractModel $object)
    {
        /** @var \Digidirect\Feed\Model\Rule $object */
        $connection = $this->getConnection();
        $table = $this->getTable('digidirect_feed_rule_feed');

        $ruleId = (int)$object->getId();
        $connection->delete($table, $connection->quoteInto('rule_id = ?', $ruleId));

        $feedIds = (array)$object->getData('feed_ids');
        if ($feedIds) {
            $data = [];
            foreach ($feedIds as $feedId) {
                $data = [
                    'rule_id' => $ruleId,
                    'feed_id' => $feedId,
                ];
            }

            $connection->insertOnDuplicate($table, $data, ['feed_id']);
        }

        return $this;
    }

    /**
     * Remove product-rule relations
     *
     * @param int $ruleId
     * @return $this
     */
    public function clearProductIds($ruleId)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable('digidirect_feed_rule_product'), ['rule_id = ?' => $ruleId]);

        return $this;
    }

    /**
     * Save product-rule relations
     *
     * @param int $ruleId
     * @param array $productIds
     * @return $this
     */
    public function saveProductIds($ruleId, array $productIds = [])
    {
        if (!$productIds) {
            return $this;
        }

        $connection = $this->getConnection();

        $data = [];
        foreach ($productIds as $productId) {
            $data[] = [
                'rule_id' => $ruleId,
                'product_id' => $productId
            ];
        }

        $connection->insertOnDuplicate($this->getTable('digidirect_feed_rule_product'), $data);

        return $this;
    }
}
