<?php
namespace Digidirect\ProductOverlay\Model\Source;

use Digidirect\ProductOverlay\Model\ResourceModel\Overlays\CollectionFactory;
use Digidirect\ProductOverlay\Model\Overlay\Attribute\Source\Status;
use Magento\Eav\Model\ResourceModel\Helper as EavHelper;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory as EavOptionFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class OverlayList
 * @package Digidirect\ProductOverlay\Model\Source
 */
class OverlayList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Eav resource helper
     *
     * @var \Magento\Eav\Model\ResourceModel\Helper
     */
    protected $_eavResourceHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory
     */
    protected $_eavOptionFactory;

    /**
     * OverlayList constructor.
     * @param CollectionFactory $_collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $_eavResourceHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $_eavOptionFactory
     */
    public function __construct(
        CollectionFactory $_collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        EavHelper $_eavResourceHelper = null,
        EavOptionFactory $_eavOptionFactory = null
    ) {
        $this->_collectionFactory = $_collectionFactory;
        $this->_storeManager = $_storeManager;
        $this->_eavResourceHelper = $_eavResourceHelper ?: ObjectManager::getInstance()->get(EavHelper::class);
        $this->_eavOptionFactory = $_eavOptionFactory ?: ObjectManager::getInstance()->get(EavOptionFactory::class);
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $collection = $this->_getOverlayCollection();
            $options = [];
            foreach ($collection as $item) {
                $options[] = [
                    'label' => $item->getName() . (!$item->getStatus() ? __(' (Disabled)') : ''),
                    'value' => $item->getOverlayId()
                ];
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

    /**
     * @return \Magento\CatalogStaging\Model\ResourceModel\Fulltext\Collection
     */
    protected function _getOverlayCollection()
    {
        $collection = $this->_collectionFactory->create();
        $collection->addStoreFilter($this->_storeManager->getStore()->getId());
        return $collection;
    }

    /**
     * @return null|array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->toOptionArray();
        }
        return $this->_options;
    }

    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        $attributeType = $this->getAttribute()->getBackendType();
        $attributeCode = $this->getAttribute()->getAttributeCode();

        return [
            $attributeCode => [
                'unsigned' => true,
                'default' => null,
                'extra' => null,
                'type' => $this->_eavResourceHelper->getDdlTypeByColumnType($attributeType),
                'nullable' => true,
            ],
        ];
    }

    /**
     * Retrieve Select for update attribute value in flat table
     *
     * @param   int $store
     * @return  \Magento\Framework\DB\Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        /** @var $option \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option */
        $option = $this->_eavOptionFactory->create();
        return $option->getFlatUpdateSelect($this->getAttribute(), $store, false);
    }
}
