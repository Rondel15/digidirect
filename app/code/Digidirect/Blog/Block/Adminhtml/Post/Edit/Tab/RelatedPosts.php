<?php
namespace Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab;

/**
 * Class RelatedPosts
 */
class RelatedPosts extends AbstractRelatedData
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'post/related_posts.phtml';

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedPosts\Grid',
                'blog.related.post'
            );
        }
        return $this->blockGrid;
    }
    
    /**
     * @return string
     */
    public function getDataJson()
    {
        $related = [];
        if ($this->getPostId()) {
            $related = $this->postRepository->getRelatedPostIds($this->getPostId());
        }
        if (!empty($related)) {
            return $this->jsonEncoder->encode($related);
        }
        return '{}';
    }
}
