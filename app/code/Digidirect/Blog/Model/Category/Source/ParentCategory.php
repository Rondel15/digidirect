<?php
namespace Digidirect\Blog\Model\Category\Source;

use Digidirect\Blog\Api\Data\CategoryInterface;
use Digidirect\Blog\Model\Category;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class ParentCategory
 */
class ParentCategory implements ArrayInterface
{
    /**
     * @var \Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * ParentCategory constructor.
     * @param \Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->options)) {
            $this->options[] = ['value' => 0, 'label' => __('Create Parent')];
            $collection = $this->collectionFactory->create();
            if ($currentCategory = $this->registry->registry(CategoryInterface::CURRENT_ITEM)) {
                $collection->addFieldToFilter('entity_id', ['nin' => $currentCategory->getId()]);
            }
            /** @var Category $category */
            foreach ($collection as $category) {
                $this->options[] = ['value' => $category->getId(), 'label' => __($category->getName())];
            }
        }
        return $this->options;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
