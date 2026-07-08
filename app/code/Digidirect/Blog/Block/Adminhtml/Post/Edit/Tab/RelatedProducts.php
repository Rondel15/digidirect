<?php
namespace Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab;

/**
 * Class RelatedProducts
 */
class RelatedProducts extends AbstractRelatedData
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'post/related_products.phtml';

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
                'Digidirect\Blog\Block\Adminhtml\Post\Edit\Tab\RelatedProducts\Grid',
                'blog.related.products'
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
            $related = $this->postRepository->getRelatedProductsIds($this->getPostId());
        }
        if (!empty($related)) {
            return $this->jsonEncoder->encode($related);
        }
        return '{}';
    }
}
