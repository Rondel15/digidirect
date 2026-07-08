<?php
namespace Digidirect\InfiniteScroll\Model\CatalogSearch;

use Magento\Catalog\Helper\Product\ProductList;

class Processor extends \Digidirect\InfiniteScroll\Model\Catalog\Processor
{
    const BLOCK_NAME = 'search_result_list';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helper->isSearchProcessorEnabled();
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return $this->_helper->getSearchActionType();
    }

    /**
     * @return int
     */
    protected function _getLimit()
    {
        return $this->_helper->getSearchLimit();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getBlock()
    {
        $block = parent::_getBlock();
        $block->setDefaultDirection(ProductList::DEFAULT_SORT_DIRECTION);
        return $block;
    }
}
