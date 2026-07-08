<?php
namespace Digidirect\SEO\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SetCategoryMetadata
 * @package Digidirect\SEO\Observer
 */
class NextPrevLinkObserver implements ObserverInterface
{
    /**
     * Meta node's types
     */
    const META_NODE_TYPE_PREVIOUS = 'prev';

    const META_NODE_TYPE_NEXT = 'next';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Default pager block
     * @var null|string
     */
    protected $pagerBlock = 'Magento\Theme\Block\Html\Pager';

    /**
     * NextPrevLinkObserver constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param null $pagerBlock
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        $pagerBlock = null
    ) {
        $this->request = $request;
        $this->pageConfig = $pageConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        if (null !== $pagerBlock) {
            $this->pagerBlock = $pagerBlock;
        }
    }

    /**
     * Execute
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $layout = $observer->getEvent()->getLayout();
        if (!$layout instanceof \Magento\Framework\View\LayoutInterface) {
            return $this;
        }

        /** @var \Magento\Catalog\Block\Product\ListProduct $catProductsBlock */
        $catProductsBlock = $layout->getBlock('category.products.list');
        if (!$catProductsBlock instanceof \Magento\Framework\View\Element\Template) {
            return $this;
        }

        $layer = $catProductsBlock->getLayer();
        if (!$layer instanceof \Magento\Catalog\Model\Layer) {
            return $this;
        }

        $filters = $layer->getState()->getFilters();
        //If request has filters skip functionality
        if (!empty($filters)) {
            return $this;
        }

        /** @var \Magento\Catalog\Block\Product\ProductList\Toolbar $toolbarBlock */
        $toolbar = $catProductsBlock->getToolbarBlock();
        if (!$toolbar instanceof \Magento\Framework\View\Element\Template) {
            return $this;
        }

        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $layout->createBlock($this->pagerBlock, uniqid(microtime()));
        if (!$pagerBlock instanceof \Magento\Framework\View\Element\Template) {
            return $this;
        }

        $limit = (int)$toolbar->getLimit();
        $pagerBlock->setAvailableLimit($toolbar->getAvailableLimit())
            ->setLimit($limit);

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addCategoryFilter($layer->getCurrentCategory());
        if ($limit) {
            $productCollection->setPageSize($limit);
        }
        $pagerBlock->setCollection($productCollection);

        if (!$pagerBlock->isFirstPage()) {
            $this->pageConfig->addRemotePageAsset(
                $pagerBlock->getPreviousPageUrl(),
                '',
                [
                    'attributes' => [
                        'rel' => self::META_NODE_TYPE_PREVIOUS,
                    ]
                ]
            );
        }

        if (!$pagerBlock->isLastPage()) {
            $this->pageConfig->addRemotePageAsset(
                $pagerBlock->getNextPageUrl(),
                '',
                [
                    'attributes' => [
                        'rel' => self::META_NODE_TYPE_NEXT,
                    ]
                ]
            );
        }

        return $this;
    }
}
