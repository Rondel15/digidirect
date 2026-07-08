<?php

namespace Digidirect\Feed\Export\Step;

use Digidirect\Feed\Export\Context;
use Digidirect\Feed\Export\Resolver\GeneralResolver;
use Digidirect\Feed\Helper\Io;
use Digidirect\Feed\Model\Config;
use Digidirect\Feed\Export\Liquid\Context as LiquidContext;
use Digidirect\Feed\Export\Liquid\Template as LiquidTemplate;
use Digidirect\Feed\Export\Filter\Pool as FilterPool;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\UrlInterfaceFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Exporting extends AbstractStep
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var GeneralResolver
     */
    protected $resolver;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Io
     */
    protected $io;

    /**
     * @var FilterPool
     */
    protected $filterPool;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterfaceFactory
     */
    protected $urlFactory;

    /**
     * Exporting constructor.
     * @param Context $context
     * @param ResourceConnection $resource
     * @param Io $io
     * @param Config $config
     * @param GeneralResolver $resolver
     * @param FilterPool $filterPool
     * @param StoreManagerInterface $storeManager
     * @param UrlInterfaceFactory $urlFactory
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Io $io,
        Config $config,
        GeneralResolver $resolver,
        FilterPool $filterPool,
        StoreManagerInterface $storeManager,
        UrlInterfaceFactory $urlFactory
    ) {
        $this->resource = $resource;
        $this->resolver = $resolver;
        $this->config = $config;
        $this->io = $io;
        $this->filterPool = $filterPool;
        $this->storeManager = $storeManager;
        $this->urlFactory = $urlFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeExecute()
    {
        parent::beforeExecute();

        $this->length = $this->resolver->getProducts()->getSize();
        $this->index = 0;
    }

    /**
     * Magento bug: every time returns admin scope, so all links for admin store
     * @see \Magento\Backend\Model\Url::_getScope()
     *
     * @param string $result
     * @return string
     */
    protected function fixBaseUrl($result)
    {
        /**
         * @var $currentStore Store
         * @var $store Store
         * @var $adminStore Store
         */
        $url = $this->urlFactory->create();
        if ($url instanceof \Magento\Backend\Model\UrlInterface) {
            $store = $this->context->getFeed()->getStore();
            $adminStore = $this->storeManager->getStore(Store::ADMIN_CODE);

            $urlTypeLink = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);

            $hasDisableStoreInUrl = $adminStore->hasDisableStoreInUrl();
            $disableStoreInUrl = $adminStore->getDisableStoreInUrl();
            $adminStore->setDisableStoreInUrl(true);
            $adminStoreUrlTypeLink = $adminStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK);
            if ($hasDisableStoreInUrl) {
                $adminStore->setDisableStoreInUrl($disableStoreInUrl);
            } else {
                $adminStore->unsDisableStoreInUrl();
            }

            if ($urlTypeLink != $adminStoreUrlTypeLink) {
                $result = str_replace($adminStoreUrlTypeLink, $urlTypeLink, $result);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if ($this->isReady()) {
            $this->beforeExecute();
        }

        $template = $this->context->getFeed()->getLiquidTemplate();

        $liquidState = [];
        if (isset($this->data['liquid'])) {
            $liquidState = $this->data['liquid'];
        }

        $liquidTemplate = new LiquidTemplate();
        $liquidTemplate->parse($template)
            ->fromArray($liquidState);

        $liquidContext = new LiquidContext($this->resolver, []);

        $liquidContext->addFilters($this->filterPool->getScopes());

        $liquidContext->setTimeoutCallback([$this->context, 'isTimeout'])
            ->setIterationCallback([$this, 'onIndexUpdate']);

        $result = $liquidTemplate->execute($liquidContext);

        $filePath = $this->config->getTmpPath() . DIRECTORY_SEPARATOR . $this->context->getFeed()->getId() . '.dat';

        // remove remove duplicate break lines
        $result = preg_replace("/[\r\n]+/", "\n", $result);

        //bug: magento generates url's for admin store... replace!
        $result = $this->fixBaseUrl($result);

        $this->io->write($filePath, $result, 'a');

        $this->data['liquid'] = $liquidTemplate->toArray();

        if ($this->isCompleted()) {
            $this->afterExecute();
        }
    }

    /**
     * Callback method for liquid template processor
     *
     * @param array $iteration
     * @return void
     */
    public function onIndexUpdate($iteration)
    {
        $this->index = $iteration['index'];
        $this->length = $iteration['length'];
    }
}
