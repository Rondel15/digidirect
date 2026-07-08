<?php
namespace Digidirect\InfiniteScroll\Model;

use Digidirect\InfiniteScroll\Model\Config\Data as InfiniteScrollConfig;

trait ProcessorTrait
{
    /**
     * @var ProcessorFactory
     */
    protected $_factory;

    /**
     * @var InfiniteScrollConfig
     */
    protected $_infiniteScrollConfig;

    /**
     * @var ProcessorInterface
     */
    protected $_processor;

    /**
     * @param string $action
     * @return ProcessorInterface
     * @throws \Exception
     */
    protected function _getProcessor($action)
    {
        if (!$this->_processor) {
            $scroll = $this->_infiniteScrollConfig->getScrollForHandle($action);
            if ($scroll) {
                $this->_processor = $this->_factory->create($scroll['instance'], ['data' => $scroll]);
            }
        }
        return $this->_processor;
    }
}
