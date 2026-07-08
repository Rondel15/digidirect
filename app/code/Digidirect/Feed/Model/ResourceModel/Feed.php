<?php

namespace Digidirect\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Phrase;
use Magento\Framework\Exception\AlreadyExistsException;

class Feed extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_feed_feed', 'feed_id');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _checkUniqueFilename(AbstractModel $object)
    {
        /** @var \Digidirect\Feed\Model\Feed $object */
        if ($filename = $object->getData(\Digidirect\Feed\Api\Data\FeedInterface::FILENAME)) {
            $select = $this->getConnection()->select()
                ->from($this->getMainTable(), [$this->getIdFieldName()])
                ->where('type = ?', $object->getType())
                ->where('filename = ?', $filename)
                ->limit(1);
            if ($object->getId()) {
                $select->where($this->getIdFieldName() . ' <> ?', $object->getId());
            }
            if ($this->getConnection()->fetchOne($select)) {
                $error = new Phrase('Feed with the same values of `type` and `filename` already exists.');
                throw new AlreadyExistsException($error);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /** @var \Digidirect\Feed\Model\Feed $object */

        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        if (!$object->getIsMassStatus()) {
            if (is_array($object->getCronDay())) {
                $object->setCronDay(implode(',', $object->getCronDay()));
            }
            if (is_array($object->getCronTime())) {
                $object->setCronTime(implode(',', $object->getCronTime()));
            }
            if (is_array($object->getNotificationEvents())) {
                $object->setNotificationEvents(implode(',', $object->getNotificationEvents()));
            }
        }

        if (!trim($object->getFilename())) {
            $object->setFilename(null);
        }

        $this->_checkUniqueFilename($object);

        return parent::_beforeSave($object);
    }

    protected function _afterSave(AbstractModel $object)
    {
        $this->_saveRules($object);

        return parent::_afterSave($object);
    }

    /**
     * Load rule ids by feed
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    public function getRuleIds(AbstractModel $object)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('digidirect_feed_rule_feed'), ['rule_id'])
            ->where('feed_id = ?', $object->getId());

        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Save rule ids
     *
     * @param AbstractModel $object
     * @return AbstractModel
     */
    protected function _saveRules(AbstractModel $object)
    {
        $table = $this->getTable('digidirect_feed_rule_feed');
        $connection = $this->getConnection();

        $feedId = (int)$object->getId();
        $connection->delete($table, $connection->quoteInto('feed_id = ?', $feedId));

        $ruleIds = (array)$object->getData('rule_ids');
        if ($ruleIds) {
            $data = [];
            foreach ($ruleIds as $ruleId) {
                $data = [
                    'feed_id' => $feedId,
                    'rule_id' => $ruleId,
                ];
            }

            $connection->insertOnDuplicate($table, $data, ['rule_id']);
        }

        return $object;
    }
}
