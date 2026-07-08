<?php
namespace Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Model\Post;

/**
 * Class AbstractRelatedData
 */
abstract class AbstractRelatedData extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * RelatedPosts constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param PostRepositoryInterface $postRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        PostRepositoryInterface $postRepository,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        $this->postRepository = $postRepository;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    abstract public function getBlockGrid();

    /**
     * @return string
     */
    abstract public function getDataJson();
    
    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->getPost()->getId();
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        return $this->registry->registry(PostInterface::CURRENT_ITEM);
    }
}
