<?php
namespace Digidirect\BlogInfiniteScroll\Model\BlogCategory;

use Digidirect\BlogInfiniteScroll\Model\Blog\Processor as BlogProcessor;

/**
 * Class Processor
 *
 * @package Digidirect\BlogInfiniteScroll\Model\BlogCategory
 */
class Processor extends BlogProcessor
{
    const BLOCK_NAME = 'blog.category';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->configHelper->isCategoryEnabled();
    }
}
