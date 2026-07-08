<?php

namespace Digidirect\InfiniteScroll\Model\Catalog;

use Digidirect\InfiniteScroll\Helper\Data as InfiniteScrollHelper;
use Digidirect\InfiniteScroll\Model\ProcessorInterface;
use Digidirect\InfiniteScroll\Model\BrandProcessor as BrandModel;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product\ProductList\Toolbar as CatalogToolbar;
use Magento\Framework\View\LayoutInterface;

class Processor implements ProcessorInterface
{
    const BLOCK_NAME = 'category.products.list';
    const ENCODING = 'UTF-8';

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
    
    protected $logger;

    /**
     * Processor constructor.
     * @param InfiniteScrollHelper $helper
     * @param LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        InfiniteScrollHelper $helper, 
        LayoutInterface $layout, 
        $data, 
        BrandModel $brandModel,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_selector = $data['selector'];
        $this->_helper = $helper;
        $this->_layout = $layout;
        $this->_brandModel = $brandModel;
        $this->logger = $logger;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function process(){
        /** @var ListProduct $block */
        $block = $this->_getBlock();

        $url = false;
        $resultHtml = "";
        $resultNode = "";

        $totalCount = 0;
        $currentCount = 0;

        $perPage = 0;
        if ($block->getLoadedProductCollection()->getSize()) {
            $totalCount = $this->getTotalSize();
            $perPage = $this->getLimit();

            $currentCount = $this->getCurrentSize();

            $url = $this->_getNextPageUrl();

            $html = $block->toHtml();

            $htmldom = new \DOMDocument();

            $processedHtml = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
            @ $htmldom->loadHTML($processedHtml);

            $x_path = new \DOMXPath($htmldom);

            $nodes = $x_path->query("//ol//li");

            foreach ($nodes as $node){
                $resultHtml .= $node->ownerDocument->saveHTML($node);

                $resultNode .= $node->nodeValue;
            }
        }

        if($currentCount > $totalCount){
            $currentCount = $totalCount;
        }

        return [
            'url' => $url,
            'content' => $resultHtml,
            'resultNode' => $resultNode,
            'totalCount' => $totalCount,
            'currentCount' => $currentCount,
            'perPageCount' => $perPage
        ];
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    protected function _getNextPageUrl()
    {
        /** @var ListProduct $block */
        $block = $this->_getBlock();

        /** @var \Digidirect\InfiniteScroll\Block\Product\ProductList\Toolbar $toolbar */
        $toolbar = $block->getToolbarBlock();
        $pager = $toolbar->getPager();

        $url = false;
        if ($pager && !$pager->isLastPage()) {
            if (strpos($_SERVER['REQUEST_URI'], "brands") !== false){
                $brand_id = 0;
            }else{
                $brand_id = $this->_brandModel->getCurrentOption();
            }
            
            //$this->logger->info('Infinite Scroll Brand ID: '. $brand_id);
            
            if($brand_id > 0){
                $page = "p=" . $toolbar->nextPageCount();

                $limit = "&_is=" .$this->getLimit();

                $url = $this->_brandModel->getCanonicalUrl() . "?" . $page;

                if (strpos($url, $limit) === false) {
                    $url = $url . $limit;
                }
            }else{
                $url = htmlspecialchars_decode($pager->getNextPageUrl());
            }
            
            //$this->logger->info('Infinite Scroll URL: '. $url);

            if (strpos($url, CatalogToolbar::DIRECTION_PARAM_NAME) === false) {
                $url .= sprintf("&%s=%s", CatalogToolbar::DIRECTION_PARAM_NAME, $toolbar->getCurrentDirection());
            }
        }

        return $url;
    }

    /**
     * @return ListProduct
     * @throws \Exception
     */
    protected function _getBlock()
    {
        $block = $this->_layout->getBlock(static::BLOCK_NAME);
        if (!$block) {
            throw new \Exception('block not found');
        }

        $block->setToolbarBlockName('infinitescroll.toolbar');
        return $block;
    }

    /**
     * @return bool|string
     */
    public function getNextPageUrl()
    {
        return $this->_getNextPageUrl();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helper->isCatalogProcessorEnabled();
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return $this->_helper->getCatalogActionType();
    }

    /**
     * @return int
     */
    protected function _getLimit()
    {
        return $this->_helper->getCatalogLimit();
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->_getLimit();
    }

    /**
     * @return int
     */
    public function getTotalSize()
    {
        $block = $this->_getBlock();
        return $block->getLoadedProductCollection()->getSize();
    }

    /**
     * @return int
     */
    public function getCurrentSize()
    {
        /** @var ListProduct $block */
        $block = $this->_getBlock();
        $collection = $block->getLoadedProductCollection();

        $limit = $this->getLimit();

        settype($limit, "integer");

        $currentPage = 1;

        $initialCurrentPage = 1;

        if(isset($_GET["p"])){
            $initialCurrentPage = $_GET["p"];
            settype($initialCurrentPage, "integer");

            $currentPage = $limit * $initialCurrentPage;
        }

        $collection->setPage($initialCurrentPage, $limit)->load();

        return $currentPage;
    }
}
