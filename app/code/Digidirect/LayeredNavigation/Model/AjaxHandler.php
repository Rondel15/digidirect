<?php
namespace Digidirect\LayeredNavigation\Model;

use Digidirect\LayeredNavigation\Helper\Data as LayeredNavigationHelper;
use Digidirect\LayeredNavigation\Helper\FilterRemember;
use Magento\Catalog\Block\Product\ProductList\Toolbar as ProductListToolbar;
use Magento\Catalog\Model\Category;
use Magento\Framework\View;
use Magento\Framework\View\LayoutInterface;

class AjaxHandler
{
    /**
     * Helper Json
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * ResponseInterface
     * @var \Magento\Framework\App\ResponseInterface|\Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * Product Listing Helper
     * @var LayeredNavigationHelper
     */
    protected $layeredNavigationHelper;

    /**
     * @var FilterRemember
     */
    protected $filterRemember;

    /**
     * Class constructor
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param LayeredNavigationHelper $helper
     * @param FilterRemember $filterRemember
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        LayeredNavigationHelper $helper,
        FilterRemember $filterRemember,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->layeredNavigationHelper = $helper;
        $this->response = $response;
        $this->filterRemember = $filterRemember;
    }

    /**
     * @param View\LayoutInterface $layout
     * @return void
     */
    public function unsetRemovableBlocks(View\LayoutInterface $layout)
    {
        $category = $this->filterRemember->getCategory();
        $displayMode = $category ? $category->getDisplayMode() : '';
        $isAnchor = $category ? $category->getIsAnchor() : true;

        if ($displayMode !== Category::DM_PAGE
            && $isAnchor
            && $this->filterRemember->isFilterRememberEnabled()
            && !$this->layeredNavigationHelper->parseRequestUri()
        ) {
            foreach ($this->layeredNavigationHelper->getRemovableBlocks() as $blockName) {
                if ($block = $layout->getBlock($blockName)) {
                    $block->setTemplate('');
                }
            }
        }
    }

    /**
     * Execute
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return false|void
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function handleLayout(LayoutInterface $layout)
    {
        if (!$this->layeredNavigationHelper->validateRequest()) {
            $this->unsetRemovableBlocks($layout);
            return false;
        }

        $response = [];
        $blocksMap = $this->layeredNavigationHelper->getBlocksMap();
        foreach ($blocksMap as $area => $blockMap) {
            $block = $layout->getBlock($blockMap[LayeredNavigationHelper::BLOCK]);
            if ($block) {
                $response[$area] = [
                    LayeredNavigationHelper::SELECTOR => $blockMap[LayeredNavigationHelper::SELECTOR],
                    LayeredNavigationHelper::BLOCK => $block->toHtml()
                ];
            }
        }

        foreach ($layout->getAllBlocks() as $block) {
            /** @var \Magento\Framework\View\Element\AbstractBlock $block */
            if ($block->getAddToLnResponse()) {
                $blockName = $block->getResponseKey() ?? $block->getNameInLayout();
                $response[$blockName] = [
                    LayeredNavigationHelper::SELECTOR => $block->getDomSelector(),
                    LayeredNavigationHelper::BLOCK => $block->toHtml()
                ];
            }
        }

        $response['page_params'] = $this->layeredNavigationHelper->getPagerParams($layout);

        $this->response
            ->clearHeaders()
            ->setNoCacheHeaders();

        $this->response
            ->representJson($this->jsonHelper->jsonEncode($response))
            ->sendResponse();

        /** @todo eliminate usage of exit statement */
        exit;
    }
}
