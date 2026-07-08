<?php
namespace Digidirect\AbstractAttributes\Model\Layer;

use Digidirect\AbstractAttributes\Api\Data\OptionInterface;

class Option extends \Magento\Catalog\Model\Layer\Category
{
    const LAYER_NAME = 'eaa_option';

    /**
     * Retrieve current layer product collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollection()
    {
        $option = $this->getCurrentOption();
        if (isset($this->_productCollections[$option->getOptionId()])) {
            $collection = $this->_productCollections[$option->getOptionId()];
        } else {
            $collection = $this->collectionProvider
                ->getCollection($this->getCurrentCategory())
                ->addFieldToFilter($option->getAttribute()->getAttributeCode(), $option->getOptionId());

            $this->prepareProductCollection($collection);
            $this->_productCollections[$option->getOptionId()] = $collection;
        }

        return $collection;
    }

    /**
     * @return OptionInterface
     */
    public function getCurrentOption()
    {
        $option = $this->_getData(self::LAYER_NAME);
        if ($option === null && ($option = $this->registry->registry('current_eaa_option'))) {
            $this->setData(self::LAYER_NAME, $option);
        }
        return $option;
    }
}
