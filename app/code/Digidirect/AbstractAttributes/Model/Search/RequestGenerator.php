<?php
namespace Digidirect\AbstractAttributes\Model\Search;

use Digidirect\AbstractAttributes\Api\AbstractAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Framework\Search\Request\BucketInterface;
use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\QueryInterface;

class RequestGenerator extends \Magento\CatalogSearch\Model\Search\RequestGenerator
{
    const EAA_OPTION_REQUEST_NAME = 'eaa_option_view_container';

    /**
     * @var AbstractAttributeRepositoryInterface
     */
    protected $abstractAttributeRepository;

    /**
     * @var []
     */
    protected $abstractAttributesCodes;

    /**
     * RequestGenerator constructor.
     * @param CollectionFactory $productAttributeCollectionFactory
     * @param AbstractAttributeRepositoryInterface $abstractAttributeRepository
     */
    public function __construct(
        CollectionFactory $productAttributeCollectionFactory,
        AbstractAttributeRepositoryInterface $abstractAttributeRepository
    ) {
        $this->abstractAttributeRepository = $abstractAttributeRepository;
        parent::__construct($productAttributeCollectionFactory);
    }

    /**
     * Generate dynamic fields requests
     * @return array
     */
    public function generate()
    {
        $searchRequest = $this->_generateRequest();
        $searchRequest = $this->_addAbstractAttributesFilters($searchRequest);

        $requests[self::EAA_OPTION_REQUEST_NAME] = $searchRequest;
        return $requests;
    }

    /**
     * @return array|null
     */
    public function getAbstractAttributesCodes()
    {
        if ($this->abstractAttributesCodes === null) {
            $this->abstractAttributesCodes = [];
            $abstractAttributes = $this->abstractAttributeRepository->getAbstractAttributes();
            foreach ($abstractAttributes as $abstractAttribute) {
                $this->abstractAttributesCodes[] = $abstractAttribute->getAttributeCode();
            }
        }
        return $this->abstractAttributesCodes;
    }

    /**
     * Generate search request
     * @return array
     */
    protected function _generateRequest()
    {
        $request = [];
        $fixedAttributes = array_merge(['price', 'category_ids'], $this->getAbstractAttributesCodes());
        foreach ($this->getSearchableAttributes() as $attribute) {
            if ($attribute->getData(EavAttributeInterface::IS_FILTERABLE)) {
                if (!in_array($attribute->getAttributeCode(), $fixedAttributes)) {
                    $queryName = $attribute->getAttributeCode() . '_query';
                    $request['queries'][self::EAA_OPTION_REQUEST_NAME]['queryReference'][] = [
                        'clause' => 'should',
                        'ref' => $queryName,
                    ];
                    $filterName = $attribute->getAttributeCode() . self::FILTER_SUFFIX;
                    $request['queries'][$queryName] = [
                        'name' => $queryName,
                        'type' => QueryInterface::TYPE_FILTER,
                        'filterReference' => [['ref' => $filterName]],
                    ];
                    $bucketName = $attribute->getAttributeCode() . self::BUCKET_SUFFIX;
                    if ($attribute->getBackendType() == 'decimal') {
                        $request['filters'][$filterName] = [
                            'type' => FilterInterface::TYPE_RANGE,
                            'name' => $filterName,
                            'field' => $attribute->getAttributeCode(),
                            'from' => '$' . $attribute->getAttributeCode() . '.from$',
                            'to' => '$' . $attribute->getAttributeCode() . '.to$',
                        ];
                        $request['aggregations'][$bucketName] = [
                            'type' => BucketInterface::TYPE_DYNAMIC,
                            'name' => $bucketName,
                            'field' => $attribute->getAttributeCode(),
                            'method' => 'manual',
                            'metric' => [['type' => 'count']],
                        ];
                    } else {
                        $request['filters'][$filterName] = [
                            'type' => FilterInterface::TYPE_TERM,
                            'name' => $filterName,
                            'field' => $attribute->getAttributeCode(),
                            'value' => '$' . $attribute->getAttributeCode() . '$',
                        ];
                        $request['aggregations'][$bucketName] = [
                            'type' => BucketInterface::TYPE_TERM,
                            'name' => $bucketName,
                            'field' => $attribute->getAttributeCode(),
                            'metric' => [["type" => "count"]],
                        ];
                    }
                }
            }

            /** @var $attribute Attribute */
            if (in_array($attribute->getAttributeCode(), ['price', 'sku'])
                || !$attribute->getIsSearchable()
            ) {
                continue;
            }
        }

        return $request;
    }

    /**
     * @param array $searchRequest
     * @return array
     */
    public function _addAbstractAttributesFilters(array $searchRequest)
    {
        $abstractAttributes = $this->getAbstractAttributesCodes();
        foreach ($abstractAttributes as $abstractAttribute) {
            $filterName = $abstractAttribute . self::FILTER_SUFFIX;
            $bucketName = $abstractAttribute . self::BUCKET_SUFFIX;

            $searchRequest['queries'][self::EAA_OPTION_REQUEST_NAME]['queryReference'][] = [
                'clause' => 'must',
                'ref' => $abstractAttribute,
            ];

            $searchRequest['queries'][$abstractAttribute] = [
                'name' => $abstractAttribute,
                'filterReference' => [[
                    'clause' => 'must',
                    'ref' => $filterName
                ]],
                'type' => 'filteredQuery'
            ];

            $searchRequest['filters'][$filterName] = [
                'name' => $filterName,
                'field' => $abstractAttribute,
                'value' => '$' . $abstractAttribute . '$',
                'type' => FilterInterface::TYPE_TERM,
            ];

            $searchRequest['aggregations'][$bucketName] = [
                'name' => $bucketName,
                'field' => $abstractAttribute,
                'metric' => [['type' => 'count']],
                'type' => BucketInterface::TYPE_TERM,
            ];
        }

        return $searchRequest;
    }
}
