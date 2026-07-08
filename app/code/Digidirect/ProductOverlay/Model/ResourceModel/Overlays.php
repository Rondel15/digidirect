<?php

namespace Digidirect\ProductOverlay\Model\ResourceModel;

use Digidirect\ProductOverlay\Api\Data\OverlayInterface;
use Magento\Framework\Model\AbstractModel;

class Overlays extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_product_overlay', 'overlay_id');
    }

    /**
     * @param int $priority
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByPriority($priority)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable())->where('pos = ?', (int)$priority);
        return $this->getConnection()->fetchAll($select);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->updateOverlayStoreRelation($object);
        parent::_afterSave($object);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function updateOverlayStoreRelation(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $setsIdsArray = $object->getStores();
            if (in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $setsIdsArray)) {
                $setsIdsArray = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
            }

            $connection = $this->getConnection();

            $setStoreTable = $this->getTable('digidirect_product_overlay_store');

            $select = $connection->select()
            ->from($setStoreTable, ['store_id'])
            ->where($connection->quoteInto('overlay_id = ?', $object->getId()));

            //$oldSetIds = $connection->fetchCol($select, ['store_id']);

            //$insert = array_diff($setsIdsArray, $oldSetIds);
            //$delete = array_diff($oldSetIds, $setsIdsArray);

            //if (!empty($delete)) {
            //    foreach ($delete as $storeId) {
            //       $condition = ['overlay_id = ?' => (int)$object->getId(), 'store_id = ?' => (int)$storeId];
            //        $connection->delete($setStoreTable, $condition);
            //    }
            //}

            //if (!empty($insert)) {
            $ids = [];
            //foreach ($setsIdsArray as $setId) {
            $ids[] = ['store_id' => 0, 'overlay_id' => $object->getId()];
            //}

            $this->getConnection()->insertOnDuplicate(
                $setStoreTable,
                $ids
            );
            //}
        }
    }

    /**
     * @return string
     */
    protected function getOverlayStoreTable()
    {
        return $this->getTable('digidirect_product_overlay_store');
    }

    /**
     * Load stores
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $object->setData('stores', $this->getStoresByOverlay($object));
        }
        parent::_afterLoad($object);
        return $this;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    protected function getStoresByOverlay(\Magento\Framework\Model\AbstractModel $object)
    {
        //clint work around 02-22-23
        return 1;
        $select = $this->getConnection()->select()
            ->from($this->getOverlayStoreTable(), ['store_id'])
            ->where($this->getConnection()->quoteInto('overlay_id =?', $object->getId()));
        return $this->getConnection()->fetchCol($select, ['store_id']);
    }
}
