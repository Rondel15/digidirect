<?php
namespace Digidirect\LayeredNavigation\Plugin\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\Framework\Search\Request\Filter\Range;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class Preprocessor
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $productAttributeRepository;

    /**
     * Preprocessor constructor.
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     */
    public function __construct(
        ProductAttributeRepositoryInterface $productAttributeRepository
    ) {
        $this->productAttributeRepository = $productAttributeRepository;
    }

    /**
     * @param Range $filter
     * @return string
     */
    protected function prepareRangeQuery(Range $filter)
    {
        $query = '';
        $field = $filter->getField();
        $from = explode(',', $filter->getFrom());
        $to = explode(',', $filter->getTo());
        if (!empty($from) && count($from) === count($to)) {
            $priceQuery = [];
            foreach ($from as $index => $fromValue) {
                $priceRangeQuery = '`' . $field . '` >= \'' . $fromValue . '\'';
                if ($to[$index]) {
                    $priceRangeQuery .= ' AND `' . $field . '` <= \'' . $to[$index] . '\'';
                }
                $priceQuery[] = new \Zend_Db_Expr($priceRangeQuery);
            }
            $query = '(' . implode(') OR (', $priceQuery) . ')';
        }
        return $query;
    }

    /**
     * @param \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject
     * @param \Closure $proceed
     * @param FilterInterface $filter
     * @param bool $isNegation
     * @param string $query
     * @return string
     */
    public function aroundProcess(
        \Magento\CatalogSearch\Model\Adapter\Mysql\Filter\Preprocessor $subject,
        $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ) {
        if ($filter->getField() == 'category_ids' && is_array($filter->getValue())) {
            $categoryIds = implode(',', array_map('intval', $filter->getValue()));
            return 'category_ids_index.category_id IN (' . $categoryIds . ')';
        }

        if ($filter->getField() == 'price') {
            /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
            return $this->prepareRangeQuery($filter);
        }

        if ($filter->getField() == 'entity_id') {
            return $proceed($filter, $isNegation, $query);
        }

        $attribute = $this->productAttributeRepository->get($filter->getField());
        if ($attribute->getBackendType() == 'decimal') {
            /** @var \Magento\Framework\Search\Request\Filter\Range $filter */
            $rangeQuery = $this->prepareRangeQuery($filter);
            if ($rangeQuery) {
                $query = $rangeQuery;
            }
        }

        return $proceed($filter, $isNegation, $query);
    }
}
