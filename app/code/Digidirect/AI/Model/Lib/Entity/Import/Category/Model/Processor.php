<?php
namespace Digidirect\AI\Model\Lib\Entity\Import\Category\Model;

use Digidirect\AI\Model\Lib\Entity\Import\Category\Model\Factory as CategoryFactory;
use Digidirect\AI\Model\Logger\LoggerInterface;
use Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\Store;

class Processor extends CategoryProcessor
{
    /**
     * Field "Name"
     */
    const COL_NAME = 'name';

    /**
     * Field "Categories Path"
     */
    const COL_PATH = 'parent_categories';

    /**
     * Field "Store ID"
     */
    const COL_STORE_ID = 'store_id';

    /**
     * @var CategoryAttributeRepositoryInterface
     */
    protected $categoryAttributeRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $categoryAttributes;

    /**
     * @var string
     */
    protected $mainTable;

    /**
     * @param CollectionFactory $categoryColFactory
     * @param CategoryFactory $categoryFactory
     * @param CategoryAttributeRepositoryInterface $categoryAttributeRepository
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string $mainTable
     */
    public function __construct(
        CollectionFactory $categoryColFactory,
        CategoryFactory $categoryFactory,
        CategoryAttributeRepositoryInterface $categoryAttributeRepository,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $mainTable = 'catalog_category_entity'
    ) {
        parent::__construct($categoryColFactory, $categoryFactory);
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->logger = $logger;
        $this->connection = $resourceConnection->getConnection();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->mainTable = $this->connection->getTableName($mainTable);
    }

    /**
     * @return array
     */
    protected function initCategoryAttributes()
    {
        if ($this->categoryAttributes === null) {
            $this->categoryAttributes = [];
            $attributes = $this->categoryAttributeRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();

            foreach ($attributes as $attribute) {
                /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
                $this->categoryAttributes[$attribute->getName()] = [
                    'table' => $attribute->getBackendTable(),
                    'attribute_id' => $attribute->getAttributeId(),
                ];
            }
        }
        return $this->categoryAttributes;
    }

    /**
     * @param array $categories
     * @param bool $updateOnDuplicate
     * @return bool
     * @throws \Exception
     */
    public function save(array $categories, $updateOnDuplicate = true)
    {
        $categoriesToUpdate = [];
        foreach ($categories as $categoryData) {
            $categoryPath = $this->buildCategoryPath($categoryData);
            if (!isset($this->categories[$categoryPath])) {
                $this->categoryFactory->setCategoryData($categoryData);
                $this->upsertCategory($categoryPath);
            } else if ($updateOnDuplicate) {
                $categoriesToUpdate[$this->categories[$categoryPath]] = $categoryData;
            }
        }
        if (!empty($categoriesToUpdate)) {
            $this->updateCategories($categoriesToUpdate);
        }
        return true;
    }

    /**
     * @param array $categories
     * @return bool
     * @throws \Exception
     */
    public function update(array $categories)
    {
        $categoriesToUpdate = [];
        foreach ($categories as $categoryData) {
            $categoryPath = $this->buildCategoryPath($categoryData);
            if (isset($this->categories[$categoryPath])) {
                $categoriesToUpdate[$this->categories[$categoryPath]] = $categoryData;
            }
        }
        if (!empty($categoriesToUpdate)) {
            $this->updateCategories($categoriesToUpdate);
        }
        return true;
    }

    /**
     * @param array $categories
     * @return bool
     * @throws \Exception
     */
    public function delete(array $categories)
    {
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryPath = $this->buildCategoryPath($category);
            if (isset($this->categories[$categoryPath])) {
                $categoryIds[] = $this->categories[$categoryPath];
            }
        }
        if (!empty($categoryIds)) {
            $this->connection->delete(
                $this->mainTable,
                $this->connection->quoteInto('entity_id IN (?)', array_unique($categoryIds))
            );
            $this->deleteUrlRewrites($categoryIds);
        } else {
            $this->logger->notice(__('No categories to delete'));
        }
        return true;
    }

    /**
     * @param array $categoryData
     * @return string
     */
    protected function buildCategoryPath(array $categoryData)
    {
        return $categoryData[self::COL_PATH] . self::DELIMITER_CATEGORY . $categoryData[self::COL_NAME];
    }

    /**
     * @param array $categories
     * @return bool
     * @throws \Exception
     */
    protected function updateCategories(array $categories)
    {
        $queries = [];
        $this->initCategoryAttributes();
        foreach ($categories as $categoryId => $data) {
            $storeId = $data[self::COL_STORE_ID] ?? Store::DEFAULT_STORE_ID;
            foreach ($data as $attribute => $value) {
                if (isset($this->categoryAttributes[$attribute])) {
                    $attributeData = $this->categoryAttributes[$attribute];
                    $queries[$attributeData['table']][] = [
                        'attribute_id' => $attributeData['attribute_id'],
                        'store_id' => $storeId,
                        'row_id' => $categoryId,
                        'value' => $value
                    ];
                }
            }
        }
        foreach ($queries as $table => $data) {
            $this->connection->insertOnDuplicate($table, $data);
        }
        return true;
    }

    /**
     * @param array $categoryIds
     * @return void
     */
    protected function deleteUrlRewrites(array $categoryIds)
    {
        $this->connection->delete(
            $this->connection->getTableName('url_rewrite'),
            $this->connection->quoteInto(
                'entity_type = \'category\' AND entity_id IN (?)',
                array_unique($categoryIds)
            )
        );
    }
}
