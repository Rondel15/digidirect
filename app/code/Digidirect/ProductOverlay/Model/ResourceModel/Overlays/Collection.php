<?php

namespace Digidirect\ProductOverlay\Model\ResourceModel\Overlays;

use Digidirect\ProductOverlay\Model\Overlays;
use \Digidirect\ProductOverlay\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'overlay_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\ProductOverlay\Model\Overlays', 'Digidirect\ProductOverlay\Model\ResourceModel\Overlays');
        $this->_map['fields'][Overlays::OVERLAY_ID] = 'main_table.overlay_id';
    }

    /**
     * Returns pairs identifier - title for unique identifiers
     * and pairs identifier|page_id - title for non-unique after first
     *
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];

        /** @var \Digidirect\ProductOverlay\Model\Overlays $item */
        foreach ($this as $item) {
            $identifier = $item->getIdentifier();

            $data['value'] = $identifier;
            $data['label'] = $item->getTitle();

            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getId();
            } else {
                $existingIdentifiers[] = $identifier;
            }

            $res[] = $data;
        }

        return $res;
    }

    /**
     * Set first store flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setFirstStoreFlag($flag = false)
    {
        $this->_previewFlag = $flag;
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId = 1)
    {
        $this->getSelect()->joinInner(
            $this->getTable('digidirect_product_overlay_store'),
            'main_table.overlay_id = digidirect_product_overlay_store.overlay_id',
            ['store_id', 'stores' => 'store_id']
        )->where(
            $this->getConnection()->quoteInto(
                'digidirect_product_overlay_store.store_id = ? OR ',
                $storeId
            ) .
            $this->getConnection()->quoteInto(
                'digidirect_product_overlay_store.store_id = ?',
                \Magento\Store\Model\Store::DEFAULT_STORE_ID
            )
        );
        $this->setOrder(Overlays::POS, 'asc');

        return $this;
    }

    /**
     * @param mixed $status
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter(Overlays::STATUS, ['eq' => $status]);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            /** @var \Digidirect\ProductOverlay\Model\Overlays $item */
            $item->getResource()->afterLoad($item);
            $item->afterLoad();
        }
        return $this;
    }
}
