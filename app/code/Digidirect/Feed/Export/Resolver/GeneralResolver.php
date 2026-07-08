<?php

namespace Digidirect\Feed\Export\Resolver;

use Digidirect\Feed\Export\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use \Magento\Framework\App\Request\Http as Request;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneralResolver extends AbstractResolver
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ReviewCollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var Request
     */
    protected $request;

    /**
     * GeneralResolver constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param PoolFactory $poolFactory
     * @param ResourceConnection $resource
     * @param ProductCollectionFactory $productCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param Request $request
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        PoolFactory $poolFactory,
        ResourceConnection $resource,
        ProductCollectionFactory $productCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        ReviewCollectionFactory $reviewCollectionFactory,
        Request $request
    ) {
        $this->resource = $resource;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->request = $request;

        parent::__construct($context, $storeManager, $filesystem, $poolFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * Store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->context->getFeed()->getStore();
    }

    /**
     * Collection of filtered products
     *
     * @param null $object
     * @param array $args
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getProducts($object = null, $args = [])
    {
        $collection = $this->productCollectionFactory->create()
            ->addStoreFilter();

        if ($this->context->isTestMode()) {
            $collection->getSelect()->limit(10);

            if ($this->request->isPost() && $this->request->getPost('preview_ids')) {
                $ids = explode(',', $this->request->getPost('preview_ids'));
            } else {
                $random = $this->resource->getConnection()->fetchAll(
                    $collection->getSelect()
                        ->limit(10)
                        ->order('rand()')
                );

                $ids = array_map(function ($item) {
                    return $item['entity_id'];
                }, $random);
            }

            $ids[] = 0;

            $collection->getSelect()
                ->reset(\Zend_Db_Select::LIMIT_COUNT)
                ->reset(\Zend_Db_Select::LIMIT_OFFSET)
                ->reset(\Zend_Db_Select::ORDER);

            $collection->addFieldToFilter('entity_id', $ids);
        } else {
            $feed = $this->context->getFeed();
            if ($feed->getRuleIds()) {
                $collection->getSelect()->joinInner(
                    ['rule' => $this->resource->getTableName('digidirect_feed_feed_product')],
                    implode(' AND ', [
                        'e.entity_id = rule.product_id',
                        $collection->getConnection()->quoteInto('rule.feed_id = ?', $feed->getId()),
                    ]),
                    []
                );
            }

            if (isset($args['index'])) {
                $collection->getSelect()->limit($args['length'], $args['index']);
                $collection->load();
            }
        }

        return $collection;
    }

    /**
     * Collection of categories
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCategories()
    {
        $collection = $this->categoryCollectionFactory->create()
            ->addIsActiveFilter();

        if ($this->context->isTestMode()) {
            $collection->getSelect()->limit(10);
        }

        return $collection;
    }

    /**
     * Collection of reviews
     *
     * @return \Magento\Review\Model\ResourceModel\Review\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getReviews()
    {
        $collection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($this->context->getFeed()->getStore()->getId())
            ->addStatusFilter(1);

        if ($this->context->isTestMode()) {
            $collection->getSelect()->limit(10);

            $random = $this->resource->getConnection()->fetchAll(
                $collection->getSelect()
                    ->limit(10)
                    ->order('rand()')
            );
            $ids = array_map(function ($item) {
                return $item['review_id'];
            }, $random);

            $collection->getSelect()
                ->reset(\Zend_Db_Select::LIMIT_COUNT)
                ->reset(\Zend_Db_Select::LIMIT_OFFSET)
                ->reset(\Zend_Db_Select::ORDER);

            $collection->addFieldToFilter('main_table.review_id', $ids);
        }

        $collection->addRateVotes();

        return $collection;
    }
}
