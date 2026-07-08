<?php
namespace Digidirect\LayeredNavigation\Plugin\Adapter\Aggregation;

use Magento\Catalog\Api\AttributeSetFinderInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\Config;
use Magento\Framework\Search\RequestInterface;

class AggregationResolver
{
    /**
     * @var AttributeSetFinderInterface
     */
    protected $attributeSetFinder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var AttributeCollection
     */
    private $attributeCollection;

    /**
     * AggregationResolver constructor.
     *
     * @param AttributeSetFinderInterface $attributeSetFinder
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Config $config
     * @param AttributeCollection|null $attributeCollection
     */
    public function __construct(
        AttributeSetFinderInterface $attributeSetFinder,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        AttributeCollection $attributeCollection = null
    ) {
        $this->attributeSetFinder = $attributeSetFinder;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
        $this->attributeCollection = $attributeCollection
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeCollection::class);
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Aggregation\AggregationResolver $subject
     * @param \Closure $proceed
     * @param RequestInterface $request
     * @param array $documentIds
     * @return array
     * @SuppressWarnings("unused")
     */
    public function aroundResolve(
        \Magento\CatalogSearch\Model\Adapter\Aggregation\AggregationResolver $subject,
        $proceed,
        RequestInterface $request,
        array $documentIds
    ) {
        if ($request->getName() != 'catalog_view_container') {
            return $proceed($request, $documentIds);
        }

        $data = $this->config->get($request->getName());

        $bucketKeys = isset($data['aggregations']) ? array_keys($data['aggregations']) : [];
        $attributeCodes = $this->getApplicableAttributeCodes($documentIds);

        $resolvedAggregation = array_filter(
            $request->getAggregation(),
            function ($bucket) use ($attributeCodes, $bucketKeys) {
                /** @var BucketInterface $bucket */
                return in_array($bucket->getField(), $attributeCodes) || in_array($bucket->getName(), $bucketKeys);
            }
        );
        return array_values($resolvedAggregation);
    }

    /**
     * Get applicable attributes
     *
     * @param array $documentIds
     * @return array
     */
    protected function getApplicableAttributeCodes(array $documentIds)
    {
        $attributeSetIds = $this->attributeSetFinder->findAttributeSetIdsByProductIds($documentIds);

        $this->attributeCollection->setAttributeSetFilter($attributeSetIds);
        $this->attributeCollection->setEntityTypeFilter(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE
        );
        $this->attributeCollection->getSelect()
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('attribute_code');

        return $this->attributeCollection->getConnection()->fetchCol($this->attributeCollection->getSelect());
    }
}
