<?php

namespace Digidirect\Feed\Model\ResourceModel\Feed;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime;

class History extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_feed_feed_history', 'history_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew() && !$object->hasCreatedAt()) {
            $object->setCreatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));
        }

        $object->setUpdatedAt((new \DateTime())->format(DateTime::DATETIME_PHP_FORMAT));

        return parent::_beforeSave($object);
    }

    /**
     * @param string $datetime
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeHistoryToDate($datetime)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            $connection->quoteInto('created_at < ?', $datetime)
        );
        
        return $this;
    }
}
