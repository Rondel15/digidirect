<?php

namespace Digidirect\Feed\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime;

class Template extends AbstractDb
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_feed_template', 'template_id');
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
     * @param array $templateIds
     * @return $this
     */
    public function removeTemplatesByIds(array $templateIds = [])
    {
        if ($templateIds) {
            $connection = $this->getConnection();
            $connection->delete(
                $this->getMainTable(),
                $connection->quoteInto($this->getIdFieldName() . ' IN (?)', $templateIds)
            );
        }

        return $this;
    }
}
