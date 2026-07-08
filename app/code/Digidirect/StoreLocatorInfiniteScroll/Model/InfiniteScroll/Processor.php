<?php

namespace Digidirect\StoreLocatorInfiniteScroll\Model\InfiniteScroll;

use Digidirect\StoreLocatorInfiniteScroll\Helper\Config as InfiniteScrollHelper;
use Digidirect\InfiniteScroll\Model\ProcessorInterface;
use Magento\Framework\View\LayoutInterface;

class Processor implements ProcessorInterface
{
    /**
     * @var string
     */
    protected $_selector;

    /**
     * @var InfiniteScrollHelper
     */
    protected $_helper;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * Processor constructor.
     * @param InfiniteScrollHelper $helper
     * @param LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        InfiniteScrollHelper $helper,
        LayoutInterface $layout,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_layout = $layout;
        $this->_selector = $data['selector'] ?? '';
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function process()
    {
        $url = false;
        $content = '';
        $totalCount = 0;
        $currentCount = 0;
        $perPageCount = 0;

        return [
            'url' => $url,
            'content' => $content,
            'totalCount' => $totalCount,
            'currentCount' => $currentCount,
            'perPageCount' => $perPageCount
        ];
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getNextPageUrl()
    {
        $url = false;
        return $url;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helper->isEnabled();
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return $this->_helper->getActionType();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->_helper->getLimit();
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getTotalSize()
    {
        return null;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function getCurrentSize($totalCount = 0)
    {
       return null;
    }
}
