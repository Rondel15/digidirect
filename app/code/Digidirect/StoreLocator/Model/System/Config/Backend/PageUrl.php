<?php

namespace Digidirect\StoreLocator\Model\System\Config\Backend;

use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Cache\TypeListInterface;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use Digidirect\StoreLocator\Model\UrlProcessor;
use Digidirect\StoreLocator\Model\UrlProcessorFactory;

/**
 * Class PageUrl
 * @package Digidirect\StoreLocator\Model\System\Config\Backend
 */
class PageUrl extends \Magento\Framework\App\Config\Value
{
    /**
     * @var UrlProcessorFactory
     */
    protected $urlProcessorFactory;

    /**
     * @var UrlProcessor
     */
    protected $urlProcessor;

    /**
     * PageUrl constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param UrlProcessorFactory $urlProcessorFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UrlProcessorFactory $urlProcessorFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->urlProcessorFactory = $urlProcessorFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     * @return $this
     */
    public function afterSave()
    {
        $urlKey = $this->getValue();

        if ($urlKey) {
            $this->_processUrlRewrites($urlKey);
        }
        return $this;
    }

    /**
     * @param string $urlKey
     * @return $this
     */
    protected function _processUrlRewrites($urlKey)
    {
        $urlProcessor = $this->getUrlProcessor();
        $urlProcessor->processUrlRewrites($urlKey);

        return $this;
    }

    /**
     * @return UrlProcessor
     */
    protected function getUrlProcessor()
    {
        if (null === $this->urlProcessor) {
            $this->urlProcessor = $this->urlProcessorFactory->create();
        }
        return $this->urlProcessor;
    }
}
