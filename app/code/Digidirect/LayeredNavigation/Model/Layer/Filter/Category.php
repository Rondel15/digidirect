<?php
namespace Digidirect\LayeredNavigation\Model\Layer\Filter;

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Catalog\Model\Layer\Filter\ItemFactory as MagentoItemFactory;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Digidirect\LayeredNavigation\Helper\UrlParser;
use Digidirect\LayeredNavigation\Helper\FilterSetting;

/**
 * Layer category filter
 */
class Category extends \Magento\CatalogSearch\Model\Layer\Filter\Category
{
    /**
     * Redeclare because of private in parent
     * @var Escaper
     */
    protected $escaper;

    /**
     * Redeclare because of private in parent
     * @var CategoryDataProvider
     */
    protected $dataProvider;

    /**
     * @var []
     */
    protected $childrenCategoriesSeoData;

    /**
     * @var \Digidirect\LayeredNavigation\Api\Data\FilterSettingInterface
     */
    protected $settingHelper;

    /**
     * Category constructor.
     * @param MagentoItemFactory $filterItemFactory
     * @param StoreManagerInterface $storeManager
     * @param Layer $layer
     * @param Layer\Filter\Item\DataBuilder $itemDataBuilder
     * @param Escaper $escaper
     * @param Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory
     * @param FilterSetting $settingHelper
     * @param array $data
     */
    public function __construct(
        MagentoItemFactory $filterItemFactory,
        StoreManagerInterface $storeManager,
        Layer $layer,
        Layer\Filter\Item\DataBuilder $itemDataBuilder,
        Escaper $escaper,
        Layer\Filter\DataProvider\CategoryFactory $categoryDataProviderFactory,
        FilterSetting $settingHelper,
        array $data = []
    ) {
        parent::__construct(
            $filterItemFactory,
            $storeManager,
            $layer,
            $itemDataBuilder,
            $escaper,
            $categoryDataProviderFactory,
            $data
        );
        $this->escaper = $escaper;
        $this->dataProvider = $categoryDataProviderFactory->create(['layer' => $this->getLayer()]);
        $this->settingHelper = $settingHelper;
    }

    /**
     * @return array
     */
    public function getChildrenCategoriesSeoData()
    {
        if ($this->childrenCategoriesSeoData === null) {
            $this->childrenCategoriesSeoData = [];
            $childCategories = $this->dataProvider->getCategory()->getChildrenCategories();
            foreach ($childCategories as $child) {
                $this->childrenCategoriesSeoData[$child->getUrlKey()] = $child->getId();
            }
        }

        return $this->childrenCategoriesSeoData;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return $this
     */
    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $categoryId = $request->getParam($this->_requestVar) ?: $request->getParam('id');
        if (empty($categoryId)) {
            return $this;
        }

        $categories = explode(UrlParser::ALIAS_DELIMITER, $categoryId);
        if (!$this->isMultiselectAllowed() && count($categories) > 1) {
            $categories = [$categories[0]];
        }

        $categoriesSeoData = $this->getChildrenCategoriesSeoData();
        foreach ($categories as $categoryUrlKey) {
            if (empty($categoryUrlKey) || !isset($categoriesSeoData[$categoryUrlKey])) {
                continue;
            }

            $catId = $categoriesSeoData[$categoryUrlKey];
            $this->dataProvider->setCategoryId($catId);
            $category = $this->dataProvider->getCategory();
            $this->getLayer()->getProductCollection()->addCategoryFilter($category, true);

            if ($request->getParam('id') != $category->getId() && $this->dataProvider->isValid()) {
                $this->getLayer()->getState()->addFilter($this->_createItem($category->getName(), $categoryUrlKey));
            }
        }

        return $this;
    }

    /**
     * @return bool|null
     */
    private function isMultiselectAllowed()
    {
        return $this->settingHelper->allowCategoryMultiSelect();
    }

    /**
     * Get data array for building category filter items
     *
     * @return array
     */
    protected function _getItemsData()
    {
        /** @var \Digidirect\LayeredNavigation\Model\ResourceModel\Fulltext\Collection $productCollection */
        $productCollection = $this->getLayer()->getProductCollection();
        $baseCategory = $this->dataProvider->getCategory();
        $collection = clone $productCollection;

        if ($productCollection->memRequestBuilder !== null) {
            $requestBuilder = clone $productCollection->memRequestBuilder;
            $collection->setRequestData($requestBuilder);
        }

        $collection->addCategoryFilter($this->getLayer()->getCurrentCategory(), true);
        $collection->clear();
        $collection->loadWithFilter();

        $optionsFacetedData = $collection->getFacetedData('category');
        $this->dataProvider->setCategoryId($this->getLayer()->getCurrentCategory()->getId());
        $category = $this->dataProvider->getCategory();

        $categories = $category->getChildrenCategories();
        $this->dataProvider->setCategoryId($baseCategory->getId());

        if ($category->getIsActive()) {
            foreach ($categories as $category) {
                if ($category->getIsActive() && isset($optionsFacetedData[$category->getId()])) {
                    $this->itemDataBuilder->addItemData(
                        $this->escaper->escapeHtml($category->getName()),
                        $category->getUrlKey(),
                        $optionsFacetedData[$category->getId()]['count']
                    );
                }
            }
        }

        return $this->itemDataBuilder->build();
    }

    /**
     * Get selected category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->dataProvider->getCategory();
    }
}
