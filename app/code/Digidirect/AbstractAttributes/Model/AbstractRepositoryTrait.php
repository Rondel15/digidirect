<?php
namespace Digidirect\AbstractAttributes\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Exception\AlreadyExistsException;

/**
 * Trait AbstractRepositoryTrait
 * @package Digidirect\AbstractAttributes\Model
 */
trait AbstractRepositoryTrait
{
    /**
     * @var array
     */
    protected $instances = [];

    /**
     * Clean internal product cache
     * @return void
     */
    public function cleanCache()
    {
        $this->instances = null;
    }

    /**
     * Clean url key
     * @param string $urlKey
     * @return string
     * @throws ValidatorException
     */
    public function cleanUrlKey($urlKey)
    {
        return preg_replace('/[^0-9a-z\_]/', '-', strtolower(trim($urlKey)));
    }

    /**
     * Validate url key
     * @param string $urlKey
     * @return true If url key is valid
     * @throws ValidatorException
     */
    public function validateUrlKey($urlKey)
    {
        if (!$urlKey) {
            $valid = false;
        } else {
            $valid = $this->_validateUrlKey($urlKey);
        }

        if (!$valid) {
            throw new ValidatorException(__('Url key is not valid'));
        }

        return true;
    }

    /**
     * Process url key
     * @param DataObject $item
     * @return string If url key is valid
     * @throws ValidatorException
     * @throws AlreadyExistsException
     */
    protected function _processUrlKey(DataObject $item)
    {
        $urlKey = $this->cleanUrlKey($item->getUrlKey());
        $this->validateUrlKey($urlKey);
        if (!$item->getId()) {
            
            
            echo "<br/><br/><br/>URL KEY: ". $urlKey ."<br/><br/><br/>";
            
            $alreadyExists = $this->getCollection()
                ->addFieldToFilter('url_key', $urlKey)
                ->setPageSize(1)
                ->getSize();

            if ($alreadyExists) {
                $item->setUrlKey('');
                throw new AlreadyExistsException(__('URL key for specified store already exists.'));
            }
        }

        $item->setUrlKey($urlKey);
        return $urlKey;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param AbstractCollection $collection
     * @return void
     */
    protected function _addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        AbstractCollection $collection
    ) {
        $fields = [];
        $conditions = [];
        foreach ($filterGroup->getFilters() as $filter) {
            if ($filter->getField() == 'store_id') {
                $collection->addStoreFilter($filter->getValue());
            } else {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
        }
        if ($fields) {
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * Regenerate Url Rewrites
     * @return void
     */
    public function regenerateUrlRewrites()
    {
        foreach ($this->getCollection() as $attribute) {
            /** @var AbstractAttribute|Option $attribute */
            $attribute->processUrlRewrites();
        }
    }
}
