<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Category\Model;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\Store;

class Factory extends CategoryFactory
{
    /**
     * @var array
     */
    protected $categoryData = [];

    /**
     * @param array $data
     * @return $this
     */
    public function setCategoryData(array $data)
    {
        $this->categoryData = $data;
        return $this;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Catalog\Model\Category
     */
    public function create(array $data = [])
    {
        $storeId = $this->categoryData[Processor::COL_STORE_ID] ?? Store::DEFAULT_STORE_ID;
        $object = parent::create($data);
        $object->setStoreId($storeId);
        $object->addData($this->categoryData);
        return $object;
    }
}
