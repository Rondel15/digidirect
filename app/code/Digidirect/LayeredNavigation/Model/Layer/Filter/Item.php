<?php
namespace Digidirect\LayeredNavigation\Model\Layer\Filter;

use Digidirect\LayeredNavigation;

class Item extends \Magento\Catalog\Model\Layer\Filter\Item
{
    /**
     * @var LayeredNavigation\Helper\UrlBuilder
     */
    protected $urlBuilderHelper;

    /**
     * Item constructor.
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Theme\Block\Html\Pager $htmlPagerBlock
     * @param LayeredNavigation\Helper\UrlBuilder $urlBuilderHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Theme\Block\Html\Pager $htmlPagerBlock,
        LayeredNavigation\Helper\UrlBuilder $urlBuilderHelper,
        array $data = []
    ) {
        $this->urlBuilderHelper = $urlBuilderHelper;
        parent::__construct($url, $htmlPagerBlock, $data);
    }

    /**
     * Get filter item url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue());
    }

    /**
     * Get url for remove item from filter
     *
     * @return string
     */
    public function getRemoveUrl()
    {
        return $this->urlBuilderHelper->buildUrl($this->getFilter(), $this->getValue(), true);
    }
}
