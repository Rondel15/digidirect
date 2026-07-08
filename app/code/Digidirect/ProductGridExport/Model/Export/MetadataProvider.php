<?php

namespace Digidirect\ProductGridExport\Model\Export;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Model\BookmarkManagement;
use Magento\Eav\Api\AttributeSetRepositoryInterface as AttributeSetRepository;
use Magento\Store\Api\WebsiteRepositoryInterface as WebsiteRepository;
use Magento\Inventory\Model\SourceItem\Command\GetSourceItemsBySku;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @var BookmarkManagement
     */
    protected $_bookmarkManagement;

    protected $attributeSetRepository;

    protected $websiteRepository;

    /**
     * @var array $columnsType
     */
    protected $columnsType;

    protected $logger;
    
    protected $getSourceItemsBySku;
    
    protected $sourceDataBySku;
    
    protected $_categoryCollectionFactory;
    /**
     * MetadataProvider constructor.
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param string $dateFormat
     * @param BookmarkManagement $bookmarkManagement
     * @param AttributeSetRepository $attributeSetRepository
     * @param WebsiteRepository $websiteRepository
     * @param array $data
     */
    public function __construct( 
        \Psr\Log\LoggerInterface $logger,
        GetSourceItemsBySku $getSourceItemsBySku,
        \Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku $sourceDataBySku,
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        BookmarkManagement $bookmarkManagement,
        AttributeSetRepository $attributeSetRepository,
        WebsiteRepository $websiteRepository,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        $dateFormat = 'M j, Y H:i:s A',
        
        array $data = [])
    {
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
        $this->_bookmarkManagement = $bookmarkManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->websiteRepository = $websiteRepository;       
        $this->logger = $logger;
        $this->getSourceItemsBySku = $getSourceItemsBySku;
        $this->sourceDataBySku = $sourceDataBySku;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    protected function getActiveColumns($component){
        $bookmark = $this->_bookmarkManagement->getByIdentifierNamespace('current', $component->getName());

        $config = $bookmark->getConfig();
        // Remove all invisible columns as well as ids, and actions columns.
        $columns = array_filter($config['current']['columns'], fn($config, $key) => $config['visible'] && !in_array($key, ['ids', 'actions']), ARRAY_FILTER_USE_BOTH);;
        // Sort by position in grid.
        uksort($columns, fn($a, $b) => $config['current']['positions'][$a] <=> $config['current']['positions'][$b]);

        return array_keys($columns);
    }

    /**
     * @param UiComponentInterface $component
     * @return UiComponentInterface[]
     * @throws \Exception
     */
    protected function getColumns(UiComponentInterface $component) : array
    {
        if (!isset($this->columns[$component->getName()])) {

            $activeColumns = $this->getActiveColumns($component);

            $columns = $this->getColumnsComponent($component);
            $components = $columns->getChildComponents();

            foreach ($activeColumns as $columnName) {
                $column = $components[$columnName] ?? null;

                if (isset($column) && $column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                    $this->columns[$component->getName()][$column->getName()] = $column;
                }
            }
        }

        return $this->columns[$component->getName()];
    }

    /**
     * @param UiComponentInterface $component
     * @return string[]
     * @throws \Exception
     */
    public function getColumnsWithDataType(UiComponentInterface $component) : array
    {
        $this->columnsType = [];
        $activeColumns = $this->getActiveColumns($component);
        $columns = $this->getColumnsComponent($component);
        $components = $columns->getChildComponents();

        foreach ($activeColumns as $columnName) {
            $column = $components[$columnName] ?? null;
            if (isset($column) && $column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                $this->columnsType[$column->getName()] = $column->getData('config/dataType');
            }
        }
        return $this->columnsType;
    }


    /**
     *
     * @param \Magento\Catalog\Model\Product $document
     * @param string[] $fields
     * @param string[] $columnsType
     *
     * @return array
     *
     */
    public function getRowDataBasedOnColumnType($document, $fields, $columnsType, $options): array{
        $rowData = array_values(
            array_map(
                function($field) use ($columnsType,$document) {
                    if ($field == 'attribute_set_id') {
                        $columnData = $this->getAttributeSetName($document, $field);
                    } elseif ($field == 'websites') {
                        $columnData = $this->getColumnData($document, $field);//$this->getWebsiteName($document, $field);
                    } elseif ($field == 'category_id') {
            
                        $categoryIds = $document->getCategoryIds();

                        $categories = $this->getCategoryCollection()->addAttributeToFilter('entity_id', $categoryIds);
                        $productCategories = "";

                        foreach ($categories as $category) {
                            $productCategories = $productCategories . $category->getName() . ", ";
                        }
                        $columnData = rtrim($productCategories, ", ");
                        
                    }  elseif (isset($columnsType[$field]) && $columnsType[$field] == 'select')  {
                        // $columnData = $this->handleSelectField($document, $field);
                        $columnData = (trim($document->getAttributeText($field))) ? trim($document->getAttributeText($field)) : $this->getColumnData($document, $field);
                    } elseif (isset($columnsType[$field]) && $columnsType[$field] == 'multiselect')  {
                        $columnData = is_array($document->getAttributeText($field)) ? implode(',',$document->getAttributeText($field)) : $document->getAttributeText($field);
                    } else {
                        $columnData = $this->getColumnData($document, $field);
                    }
                    return $columnData;
                },
            $fields)
        );
        return $rowData;
    }

    public function getRowData($document, $fields, $options): array{
        $rowData = array_values(array_map(fn($field) => $this->getColumnData($document, $field), $fields));
        return $rowData;
    }

    public function getColumnData($document, $field)
    {
        $value = $document->getData($field);

        if (is_array($value)) {
            $this->logger->info(implode('field: ' . $field . ', value:' . $value));
            return implode(', ', $value);
        }
        
        //$this->logger->info('field: ' . $field . ', value:' . $value);
        
        if ($field == "quantity_per_source") {
            
            $sku = $document->getData('sku');
            $sourceItems = $this->sourceDataBySku->execute($sku);
            $qps = '';
            foreach ($sourceItems as $sourceItem) {
                $this->logger->info($sourceItem['name']);
                $getQty = $sourceItem['quantity'];
                $store = $sourceItem['name'];
                $this->logger->info('store: ' . $store . ', getQty:' . $getQty);
                $qps .= $store . ": " . $getQty . ",";
            }
            return rtrim($qps, ",");
            
        }
        
        return $value;
    }
    
    public function getCategoryCollection()
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');        
        return $collection;
    }

    /**
     *
     * handler of select fields attribute
     *
     * @param \Magento\Catalog\Model\Product $_productItem
     * @param string $field
     *
     * @return string $columnData
     *
     */
    protected function handleSelectField(\Magento\Catalog\Model\Product $_productItem, string $field):string {
        if (trim($_productItem->getAttributeText($field))) {
            $columnData = trim($_productItem->getAttributeText($field));
        }  else {
            $columnData = $this->getColumnData($_productItem, $field);
        }
        return (string) $columnData;
    }
    
    /**
     *
     * @param \Magento\Catalog\Model\Product $_productItem
     * @param string $field
     *
     * @return string $attributeSetName
     *
     */
    protected function getAttributeSetName(\Magento\Catalog\Model\Product $_productItem, string $field):string {
        $attributeSetId = $_productItem->getData($field);
        /** @var $_attributeSet \Magento\Eav\Api\Data\AttributeSetInterface */
        $_attributeSet = $this->attributeSetRepository->get($attributeSetId);
        $attributeSetName = ($_attributeSet) ? $_attributeSet->getAttributeSetName() : '';
        return $attributeSetName;
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $_productItem
     * @param string $field
     *
     * @return string $websiteName
     *
     */
    protected function getWebsiteName(\Magento\Catalog\Model\Product $_productItem, string $field):string {
        $websiteId = $this->getColumnData($_productItem,$field);
        /** @var $_website \Magento\Store\Api\Data\WebsiteInterface */
        $_website = $this->websiteRepository->getById($websiteId);
        $websiteName = ($_website) ? $_website->getName() : '';
        return $websiteName;
    }

}
