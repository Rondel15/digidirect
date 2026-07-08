<?php

namespace Digidirect\Blog\Ui\DataProvider\Post\Form;

use Digidirect\Blog\Ui\DataProvider\AbstractFormDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Digidirect\Blog\Model\Post;
use Digidirect\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

/**
 * Class PostDataProvider
 */
class PostDataProvider extends AbstractFormDataProvider
{
    const FORM_COMPONENT = 'blog_post_form';

    /**
     * PostDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param PostCollectionFactory $postCollectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     * @param array $fieldsetConfiguration
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        PostCollectionFactory $postCollectionFactory,
        PoolInterface $pool,
        array $meta = [],
        array $data = [],
        array $fieldsetConfiguration = []
    ) {
        $this->collection = $postCollectionFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $pool,
            $meta,
            $data,
            $fieldsetConfiguration
        );
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
         * @var Post $item
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
