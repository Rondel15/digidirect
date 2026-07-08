<?php
namespace Digidirect\BlogInfiniteScroll\Model\Blog;

use Digidirect\InfiniteScroll\Model\ProcessorInterface as InfiniteScrollProcessorInterface;
use Digidirect\BlogInfiniteScroll\Helper\Config;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutInterface;
use Digidirect\Blog\Block\Blog;

/**
 * Class Processor
 *
 * @package Digidirect\BlogInfiniteScroll\Model\Blog
 */
class Processor implements InfiniteScrollProcessorInterface
{
    const BLOCK_NAME = 'blog.list';

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Processor constructor.
     *
     * @param Config $configHelper
     * @param LayoutInterface $layout
     */
    public function __construct(Config $configHelper, LayoutInterface $layout)
    {
        $this->configHelper = $configHelper;
        $this->layout = $layout;
    }

    /**
     * @return array
     */
    public function process()
    {
        $block = $this->getBlock();
        $url = '';
        $content = '';
        $currentCount = '';
        $totalCount = '';
        $perPageCount = '';

        if ($block instanceof Blog) {
            $blogCollection = $block->getCollection();
            if ($blogCollection->getSize()) {
                $url = $this->getNextPageUrl();
                $content = $block->toHtml();
                $totalCount = $this->getTotalSize();
                $currentCount = $this->getCurrentSize();
                $perPageCount = $this->getLimit();
            }
        }
        return [
            'url' => $url,
            'content' => $content,
            'totalCount' => $totalCount,
            'currentCount' => $currentCount,
            'perPageCount' => $perPageCount,
        ];
    }

    /**
     * @return int
     */
    public function getCurrentSize()
    {
        $block = $this->getBlock();
        if (!($block instanceof Blog)) {
            return 0;
        }
        /* @var $block \Digidirect\Blog\Block\Blog */
        $collection = $block->getCollection();
        return $collection->count() + ($this->getLimit() * ($collection->getCurPage() - 1));
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->configHelper->getLimit();
    }

    /**
     * @return string
     */
    public function getNextPageUrl()
    {
        $block = $this->getBlock();
        if (!($block instanceof Blog)) {
            return '';
        }
        /**
         * @var $toolbar \Magento\Theme\Block\Html\Pager
         */
        $toolbar = $this->getToolbarBlock($block);
        $url = '';
        if ($toolbar && !$toolbar->isLastPage()) {
            $url = htmlspecialchars_decode($toolbar->getNextPageUrl());
        }
        return $url;
    }

    /**
     * @return string
     */
    public function getActionType()
    {
        return $this->configHelper->getActionType();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->configHelper->isPostEnabled();
    }

    /**
     * @return int
     */
    public function getTotalSize()
    {
        $block = $this->getBlock();
        return $this->getToolbarBlock($block)->getTotalNum();
    }

    /**
     * @return bool|\Digidirect\Blog\Block\Blog
     * @throws \Exception
     */
    protected function getBlock()
    {
        $block = $this->layout->getBlock(static::BLOCK_NAME);
        if (!$block) {
            throw new \Exception('block not found');
        }
        return $block;
    }

    /**
     * @param Blog|null $block
     * @return bool|DataObject|\Magento\Theme\Block\Html\Pager
     */
    protected function getToolbarBlock(Blog $block = null)
    {
        if (!$block || !($block instanceof Blog)) {
            return new DataObject();
        }

        return $block->getChildBlock('toolbar');
    }
}
