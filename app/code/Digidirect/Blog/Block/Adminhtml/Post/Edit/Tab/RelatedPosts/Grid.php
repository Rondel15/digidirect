<?php
namespace Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedPosts;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Model\Config\Provider\Status;
use Digidirect\Blog\Model\Post;
use Digidirect\Blog\Model\ResourceModel\Post\Collection;
use Digidirect\Blog\Model\ResourceModel\Post\CollectionFactory;
use Magento\Backend\Block\Widget\Grid\Column;

/**
 * Class Grid
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var Status
     */
    protected $status;

    /**
     * Grid constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param PostRepositoryInterface $attachmentRepository
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        PostRepositoryInterface $attachmentRepository,
        Status $status,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->postRepository = $attachmentRepository;
        $this->status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('blog_related_posts');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getPost() && $this->getPost()->getId()) {
            $this->setDefaultFilter(['in_post' => 1]);
        }
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_post') {
            $postsIds = $this->getSelectedPosts();
            if (empty($postsIds)) {
                $postsIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $postsIds]);
            } else {
                if ($postsIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $postsIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addPositionToSelect($this->getPostId());
        $collection->orderByPosition();
        $collection->groupByEntityId();
        $collection->addFieldToFilter('entity_id', ['nin' => $this->getPostId()]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_post',
            [
                'type' => 'checkbox',
                'name' => 'in_post',
                'values' => $this->getSelectedPosts(),
                'index' => 'entity_id',
                'sortable' => false,
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'index' => 'entity_id',
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'url_key',
            [
                'header' => __('Url Key'),
                'index' => 'url_key',
                'sortable' => false,
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status',
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'filter' => false,
                'sortable' => false,
                'editable' => true,
                'edit_only' => true
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('digidirect_blog/post/posts', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function getSelectedPosts()
    {
        $related = $this->postRepository->getRelatedPostIds($this->getPostId());
        return array_keys($related);
    }

    /**
     * @return Post
     */
    protected function getPost()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @return int
     */
    protected function getPostId()
    {
        return $this->getPost()->getId();
    }
}
