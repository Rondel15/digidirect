<?php

namespace Digidirect\Feed\Block\Adminhtml\Feed;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Registry;
use Digidirect\Feed\Export\Resolver\ProductResolver;
use Digidirect\Feed\Helper\Data as FeedHelper;
use Digidirect\Feed\Export\Liquid\Context as LiquidContext;
use Digidirect\Feed\Export\Liquid\Template as LiquidTemplate;

class Library extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FeedHelper
     */
    protected $dataHelper;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductResolver
     */
    protected $productResolver;

    /**
     * Library constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FeedHelper $dataHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductResolver $productResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FeedHelper $dataHelper,
        ProductCollectionFactory $productCollectionFactory,
        ProductResolver $productResolver,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->dataHelper = $dataHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productResolver = $productResolver;

        parent::__construct($context, $data);
    }

    /**
     * Collection of random products
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getRandomProducts()
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->setPageSize(5);
        $collection->getSelect()->orderRand();

        return $collection;
    }

    /**
     * Get pattern value for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getPatternValue($product)
    {
        $liquidTemplate = new LiquidTemplate();
        $liquidTemplate->parse('{{ product.' . $this->getData('pattern') . ' }}');

        $liquidContext = new LiquidContext($this->productResolver, ['product' => $product]);

        //$liquidContext->addFilters($this->filterPool->getScopes());

        return $liquidTemplate->execute($liquidContext);
    }
}
