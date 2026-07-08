<?php
namespace Digidirect\AI\Preferences\Model\Import\Product;

/**
 * Class CategoryProcessor
 *
 * @package Digidirect\AI\Preferences\Model\Import\Product
 */
class CategoryProcessor extends \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
{
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryColFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->categoryColFactory = $categoryColFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @param null $storeId
     * @return $this
     */
    public function initCategories($storeId = null)
    {
        if (empty($this->categories[$storeId])) {
            $collection = $this->categoryColFactory->create();
            $collection->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToSelect('url_path');
            if ($storeId !== null) {
                $collection->setStoreId($storeId);
            }
            /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
            foreach ($collection as $category) {
                $structure = explode(self::DELIMITER_CATEGORY, $category->getPath());
                $pathSize = count($structure);

                $this->categoriesCache[$category->getId()] = $category;
                if ($pathSize > 1) {
                    $path = [];
                    for ($i = 1; $i < $pathSize; $i++) {
                        $path[] = $collection->getItemById((int)$structure[$i])->getName();
                    }
                    /** @var string $index */
                    $index = $this->standardizeString(
                        implode(self::DELIMITER_CATEGORY, $path)
                    );
                    $this->categories[$storeId][$index] = $category->getId();
                }
            }
        }
        return $this;
    }

    /**
     * Creates a category.
     *
     * @param string $name
     * @param int $parentId
     * @param string $storeId
     * @return int
     */
    protected function createCategory($name, $parentId, $storeId = null)
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->categoryFactory->create();
        if (!($parentCategory = $this->getCategoryById($parentId))) {
            $parentCategory = $this->categoryFactory->create()->load($parentId);
        }
        $category->setPath($parentCategory->getPath());
        $category->setParentId($parentId);
        $category->setName($name);
        $category->setIsActive(true);
        $category->setIncludeInMenu(true);
        $category->setAttributeSetId($category->getDefaultAttributeSetId());
        if ($storeId !== null) {
            $category->setStoreId($storeId);
        }
        try {
            $category->save();
            $this->categoriesCache[$category->getId()] = $category;
        } catch (\Throwable $e) {
            $this->addFailedCategory($category, $e);
        }

        return $category->getId();
    }

    /**
     * Returns ID of category by string path creating nonexistent ones.
     *
     * @param string $categoryPath
     * @param string $storeId
     *
     * @return int
     */
    protected function upsertCategory($categoryPath, $storeId = null)
    {
        /** @var string $index */
        $index = $this->standardizeString($categoryPath);

        if (!isset($this->categories[$storeId][$index])) {
            $pathParts = explode(self::DELIMITER_CATEGORY, $categoryPath);
            $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $path = '';

            foreach ($pathParts as $pathPart) {
                $path .= $this->standardizeString($pathPart);
                if (!isset($this->categories[$storeId][$path])) {
                    $this->categories[$storeId][$path] = $this->createCategory($pathPart, $parentId, $storeId);
                }
                $parentId = $this->categories[$storeId][$path];
                $path .= self::DELIMITER_CATEGORY;
            }
        }

        return $this->categories[$storeId][$index];
    }

    /**
     * Returns IDs of categories by string path creating nonexistent ones.
     *
     * @param string $categoriesString
     * @param string $categoriesSeparator
     * @param string $storeId
     *
     * @return array
     */
    public function upsertCategories($categoriesString, $categoriesSeparator, $storeId = null)
    {
        $this->initCategories($storeId);
        $categoriesIds = [];
        $categories = explode($categoriesSeparator, $categoriesString);

        foreach ($categories as $category) {
            try {
                $categoriesIds[] = $this->upsertCategory($category, $storeId);
            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->addFailedCategory($category, $e);
            }
        }

        return $categoriesIds;
    }

    /**
     * Add failed category
     *
     * @param string $category
     * @param \Magento\Framework\Exception\LocalizedException $exception
     *
     * @return $this
     */
    protected function addFailedCategory($category, $exception)
    {
        $this->failedCategories[] =
            [
                'category' => $category,
                'exception' => $exception,
            ];
        return $this;
    }

    /**
     * Standardize a string.
     * For now it performs only a lowercase action, this method is here to include more complex checks in the future
     * if needed.
     *
     * @param string $string
     * @return string
     */
    protected function standardizeString($string)
    {
        return mb_strtolower($string);
    }
}
