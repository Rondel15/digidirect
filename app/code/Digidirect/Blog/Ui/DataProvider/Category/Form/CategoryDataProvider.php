<?php

namespace Digidirect\Blog\Ui\DataProvider\Category\Form;

use Digidirect\Blog\Ui\DataProvider\AbstractFormDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Digidirect\Blog\Model\Category;
use Digidirect\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Class CategoryDataProvider
 */
class CategoryDataProvider extends AbstractFormDataProvider
{
    const FORM_COMPONENT = 'blog_category_form';

    /**
     * CategoryDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     * @param array $fieldsetConfiguration
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CategoryCollectionFactory $categoryCollectionFactory,
        PoolInterface $pool,
        array $meta = [],
        array $data = [],
        array $fieldsetConfiguration = []
    ) {
        $this->collection = $categoryCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $pool, $meta, $data, $fieldsetConfiguration);
    }

    /**
     * Used to be able modify data
     *
     * @return array
     */
    public function getData()
    {
        $items = $this->collection->getItems();
        /**
         * @var Category $item
         */
        foreach ($items as $item) {
            $result = $item->getData();
            $this->data[$item->getId()] = $this->extractData($result);
        }
        /** @var ModifierInterface $modifier */
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->data = $modifier->modifyData($this->data ?? []);
        }
        return $this->data;
    }
}
