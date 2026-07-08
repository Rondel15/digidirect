<?php
namespace Digidirect\InfiniteScroll\Block;

use Digidirect\InfiniteScroll\Helper\Data as InfiniteScrollHelper;
use Digidirect\InfiniteScroll\Model\Config\Data as InfiniteScrollConfig;
use Digidirect\InfiniteScroll\Model\ProcessorFactory;
use Digidirect\InfiniteScroll\Model\ProcessorTrait;

class Core extends \Magento\Framework\View\Element\Template
{
    use ProcessorTrait;

    /**
     * @var array
     */
    protected $_scroll;

    /**
     * @var InfiniteScrollHelper
     */
    protected $_helper;

    /**
     * Core constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param ProcessorFactory $factory
     * @param InfiniteScrollConfig $config
     * @param InfiniteScrollHelper $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ProcessorFactory $factory,
        InfiniteScrollConfig $config,
        InfiniteScrollHelper $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_factory = $factory;
        $this->_infiniteScrollConfig = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return \Digidirect\InfiniteScroll\Model\ProcessorInterface
     */
    private function _initProcessor()
    {
        $scroll = $this->_getScroll();
        return $this->_getProcessor($scroll['handle']);
    }

    /**
     * @return array|bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getScroll()
    {
        if (!$this->_scroll) {
            $handles = $this->getLayout()->getUpdate()->getHandles();
            foreach ($handles as $handle) {
                $scroll = $this->_infiniteScrollConfig->getScrollForHandle($handle);
                if ($scroll) {
                    $this->_scroll = $scroll;
                    break;
                }
            }
            if (!$this->_scroll) {
                throw new \Exception('Infinite scroll for not found');
            }
        }
        return $this->_scroll;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSelector()
    {
        $scroll = $this->_getScroll();
        return $scroll['selector'];
    }

    /**
     * @return string
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextPageUrl()
    {
        $processor = $this->_initProcessor();
        $url = $processor->getNextPageUrl();
        if ($url) {
            $url .= sprintf("&%s=%s", InfiniteScrollHelper::PARAM_NAME, $processor->getLimit());
        }
        return $url;
    }

    /**
     * @return int
     */
    public function getActionType()
    {
        return $this->_initProcessor()->getActionType();
    }

    /**
     * @return int
     */
    public function getPerPageCount()
    {
        return $this->_initProcessor()->getLimit();
    }

    /**
     * @return int
     */
    public function getCurrentCount()
    {
        return $this->_initProcessor()->getCurrentSize();
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->_initProcessor()->getTotalSize();
    }
}
