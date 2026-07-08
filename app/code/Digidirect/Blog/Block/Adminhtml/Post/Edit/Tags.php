<?php
namespace Digidirect\Blog\Block\Adminhtml\Post\Edit;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Model\ResourceModel\Tag\CollectionFactory;

/**
 * Class Tags
 */
class Tags extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Digidirect_Blog::post/tags.phtml';

    /**
     * @var CollectionFactory
     */
    protected $tagCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Tags constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $tagCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CollectionFactory $tagCollectionFactory,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->tagCollectionFactory = $tagCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return \Digidirect\Blog\Model\ResourceModel\Tag\Collection
     */
    public function getCollection()
    {
        $post = $this->registry->registry(PostInterface::CURRENT_ITEM);
        $collection = $this->tagCollectionFactory->create();
        if (!empty($post->getTagId())) {
            $collection->addFieldToFilter('entity_id', ['nin' => $post->getTagId()]);
        }
        return $collection;
    }
}
