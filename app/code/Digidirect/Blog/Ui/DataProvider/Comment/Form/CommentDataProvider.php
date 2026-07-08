<?php
namespace  Digidirect\Blog\Ui\DataProvider\Comment\Form;

use Digidirect\Blog\Ui\DataProvider\AbstractFormDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Digidirect\Blog\Model\Comment;
use Digidirect\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;

/**
 * Class CommentDataProvider
 */
class CommentDataProvider extends AbstractFormDataProvider
{
    const FORM_COMPONENT = 'blog_comment_form';

    /**
     * CommentDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CommentCollectionFactory $categoryCollectionFactory
     * @param PoolInterface $pool
     * @param array $meta
     * @param array $data
     * @param array $fieldsetConfiguration
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CommentCollectionFactory $categoryCollectionFactory,
        PoolInterface $pool,
        array $meta = [],
        array $data = [],
        array $fieldsetConfiguration = []
    ) {
        $this->collection = $categoryCollectionFactory->create();
        parent::__construct(
            $name,
            'main_table.' . $primaryFieldName,
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
         * @var Comment $item
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
