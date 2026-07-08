<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Model;

use Exception;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as EavCollectionFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filter\FilterManager;
use Magento\Store\Model\Store;
use Mageplaza\Shopbybrand\Api\BrandRepositoryInterface;
use Mageplaza\Shopbybrand\Api\Data\BrandCategoryInterface;
use Mageplaza\Shopbybrand\Api\Data\BrandInterface;
use Mageplaza\Shopbybrand\Helper\Data as Helper;
use Mageplaza\Shopbybrand\Model\Config\BrandInfo;
use Mageplaza\Shopbybrand\Model\Config\BrandsPageSettings;
use Mageplaza\Shopbybrand\Model\Config\General;
use Mageplaza\Shopbybrand\Model\Config\Seo;
use Mageplaza\Shopbybrand\Model\Config\Sidebar;
use Mageplaza\Shopbybrand\Model\ResourceModel\Brand as BrandResourceModel;
use Mageplaza\Shopbybrand\Model\ResourceModel\Category as BrandCategoryResource;
use Mageplaza\Shopbybrand\Model\ResourceModel\Category\Collection;

/**
 * Class BrandRepository
 * @package Mageplaza\Shopbybrand\Model
 */
class BrandRepository implements BrandRepositoryInterface
{
    /**
     * @var EavCollectionFactory
     */
    protected $_attrOptionCollectionFactory;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var AttributeOptionManagementInterface
     */
    protected $eavOptionManagement;

    /**
     * @var BrandFactory
     */
    protected $brandFactory;

    /**
     * @var BrandResourceModel
     */
    protected $resourceModel;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var Attribute
     */
    protected $eavResourceModel;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var FilterManager
     */
    protected $filter;

    /**
     * @var EavAttribute
     */
    protected $eavAttribute;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Visibility
     */
    protected $visibleProducts;

    /**
     * @var BrandCategoryResource
     */
    protected $brandCategoryResource;

    /**
     * @var BrandConfigFactory
     */
    protected $brandConfigFactory;

    /**
     * @var BrandConfig
     */
    protected $brandConfig;

    /**
     * BrandRepository constructor.
     *
     * @param EavAttribute $eavAttribute
     * @param EavCollectionFactory $attrOptionCollectionFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AttributeOptionManagementInterface $eavOptionManagement
     * @param BrandFactory $brandFactory
     * @param BrandResourceModel $resourceModel
     * @param BrandCategoryResource $brandCategoryResource
     * @param AttributeRepository $attributeRepository
     * @param Attribute $eavResourceModel
     * @param CategoryFactory $categoryFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param Visibility $visibleProducts
     * @param FilterManager $filter
     * @param BrandConfigFactory $brandConfigFactory
     * @param Helper $helper
     * @param BrandConfig $brandConfig
     */
    public function __construct(
        EavAttribute $eavAttribute,
        EavCollectionFactory $attrOptionCollectionFactory,
        ProductRepositoryInterface $productRepository,
        AttributeOptionManagementInterface $eavOptionManagement,
        BrandFactory $brandFactory,
        BrandResourceModel $resourceModel,
        BrandCategoryResource $brandCategoryResource,
        AttributeRepository $attributeRepository,
        Attribute $eavResourceModel,
        CategoryFactory $categoryFactory,
        ProductCollectionFactory $productCollectionFactory,
        Visibility $visibleProducts,
        FilterManager $filter,
        BrandConfigFactory $brandConfigFactory,
        Helper $helper,
        BrandConfig $brandConfig
    ) {
        $this->eavAttribute                 = $eavAttribute;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productRepository            = $productRepository;
        $this->eavOptionManagement          = $eavOptionManagement;
        $this->brandFactory                 = $brandFactory;
        $this->resourceModel                = $resourceModel;
        $this->helper                       = $helper;
        $this->attributeRepository          = $attributeRepository;
        $this->eavResourceModel             = $eavResourceModel;
        $this->categoryFactory              = $categoryFactory;
        $this->filter                       = $filter;
        $this->productCollectionFactory     = $productCollectionFactory;
        $this->visibleProducts              = $visibleProducts;
        $this->brandCategoryResource        = $brandCategoryResource;
        $this->brandConfigFactory           = $brandConfigFactory;
        $this->brandConfig                  = $brandConfig;
    }

    /**
     * @inheritdoc
     */
    public function getBrandList($storeId = null)
    {
        $collection = $this->helper->getBrandList(null, null, $storeId);
        /** @var Brand $brand */
        foreach ($collection->getItems() as $brand) {
            $brand->setProductQuantity(count($this->getProductList($brand->getOptionId())));
        }

        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getFeatureBrand()
    {
        $collection = $this->helper->getBrandList();
        $collection->addFieldToFilter('is_featured', 1);
        /** @var Brand $brand */
        foreach ($collection->getItems() as $brand) {
            $brand->setProductQuantity(count($this->getProductList($brand->getOptionId())));
        }

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getBrandByName($name)
    {
        $collection = $this->helper->getBrandList();
        $collection->addFieldToFilter('tdv.value', ['like' => $name . '%']);
        /** @var Brand $brand */
        foreach ($collection->getItems() as $brand) {
            $brand->setProductQuantity(count($this->getProductList($brand->getOptionId())));
        }

        return $collection->getItems();
    }

    /**
     * @inheritDoc
     */
    public function getProductList($optionId)
    {
        $attCode    = $this->helper->getAttributeCode();
        $collection = $this->productCollectionFactory->create()
            ->setVisibility($this->visibleProducts->getVisibleInCatalogIds())
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('status', 1)
            ->addAttributeToFilter($attCode, $optionId);

        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getBrandBySku($sku, $storeId = null)
    {
        $product  = $this->productRepository->get($sku, false, $storeId);
        $optionId = $product->getData($this->helper->getAttributeCode($storeId));
        $brand    = $this->brandFactory->create();
        $this->resourceModel->load($brand, $optionId, 'option_id');
        $brand->setProductQuantity(count($this->getProductList($brand->getOptionId())));

        return $brand;
    }

    /**
     * @inheritdoc
     */
    public function setProduct($optionId, $sku, $storeId = null)
    {
        $product = $this->productRepository->get($sku, false, $storeId);
        $product->setData($this->helper->getAttributeCode($storeId), $optionId);
        $this->productRepository->save($product);

        return $this->productRepository->get($sku, false, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function deleteProduct($sku)
    {
        $product = $this->productRepository->get($sku);
        $product->setData($this->helper->getAttributeCode(), '');
        $this->productRepository->save($product);

        return true;
    }

    /**
     * @return AttributeOptionInterface[]
     * @throws InputException
     * @throws StateException
     */
    public function getItems()
    {
        return $this->eavOptionManagement->getItems(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->helper->getAttributeCode()
        );
    }

    /**
     * Add option to attribute
     *
     * @param BrandInterface $option
     *
     * @return bool
     * @throws StateException
     * @throws NoSuchEntityException
     */
    public function addOption($option)
    {
        $attributeCode = $this->helper->getAttributeCode();
        $attribute     = $this->attributeRepository->get(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );
        if (!$attribute->usesSource()) {
            throw new StateException(__('The "%1" attribute doesn\'t work with options.', $attributeCode));
        }

        $optionLabel                    = $option->getLabel();
        $optionId                       = 'id_' . ($option->getValue() ?: 'new_option');
        $options                        = [];
        $options['value'][$optionId][0] = $optionLabel;
        $options['order'][$optionId]    = $option->getSortOrder();

        if (is_array($option->getStoreLabels())) {
            foreach ($option->getStoreLabels() as $label) {
                $options['value'][$optionId][$label->getStoreId()] = $label->getLabel();
            }
        }

        if ($option->getIsDefault()) {
            $attribute->setDefault([$optionId]);
        }

        $attribute->setOption($options);
        try {
            $this->eavResourceModel->save($attribute);
            $attributeSource = $attribute->getSource();
            if (!$attributeSource) {
                return false;
            }
            if ($optionLabel && $attribute->getAttributeCode()) {
                $optionId = $attributeSource->getOptionId($optionLabel);
                if ($optionId) {
                    $option->setValue($attributeSource->getOptionId($optionId));
                } elseif (is_array($option->getStoreLabels())) {
                    foreach ($option->getStoreLabels() as $label) {
                        if ($optionId = $attributeSource->getOptionId($label->getLabel())) {
                            $option->setValue($attributeSource->getOptionId($optionId));
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            throw new StateException(__('The "%1" attribute can\'t be saved.', $attributeCode));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function add($option)
    {
        /** @var BrandInterface[] $currentOptions */
        $currentOptions = $this->getItems();
        if (is_array($currentOptions)) {
            array_walk($currentOptions, function (&$attributeOption) {
                /** @var BrandInterface $attributeOption */
                $attributeOption = $attributeOption->getLabel();
            });
            if (in_array($option->getLabel(), $currentOptions, true)) {
                return false;
            }
        }
        if ($this->addOption($option)) {
            $defaultStore = Store::DEFAULT_STORE_ID;
            $optionId     = $option->getValue();
            $option->setOptionId($option->getValue());
            $option = Helper::jsonDecode(Helper::jsonEncode($option));
            try {
                /** @var Brand $brand */
                $brand = $this->brandFactory->create();
                $this->resourceModel->load($brand, $optionId, 'option_id');
                if (!$brand->getId()) {
                    $brand->addData($option)
                        ->setStoreId($defaultStore);
                    $this->resourceModel->save($brand);
                }
                $brand->addData($option)->setId($brand->getId());
                $this->resourceModel->save($brand);
            } catch (Exception $e) {
                throw new StateException(__('The brand can\'t be saved.'));
            }

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function update($optionId, $option)
    {
        if (empty($optionId)) {
            throw new InputException(__('Invalid option id %1', $optionId));
        }

        try {
            /** @var Brand $brand */
            $brand = $this->brandFactory->create();
            $this->resourceModel->load($brand, $optionId, 'option_id');
            if (!$brand->getId()) {
                throw new NoSuchEntityException(
                    __(
                        'The "%1" brand doesn\'t exist.',
                        $optionId
                    )
                );
            }
            $option = Helper::jsonDecode(Helper::jsonEncode($option));
            $brand->addData($option)->setId($brand->getId());
            $this->resourceModel->save($brand);
        } catch (Exception $e) {
            throw new StateException(__('The brand can\'t be updated.'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete($optionId)
    {
        if (empty($optionId)) {
            throw new InputException(__('Invalid option id %1', $optionId));
        }

        return $this->eavOptionManagement->delete(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->helper->getAttributeCode(),
            $optionId
        );
    }

    /**
     * @inheritdoc
     */
    public function getCategory()
    {
        /** @var Collection $collection */
        $collection = $this->categoryFactory->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @inheritdoc
     */
    public function getCategoryById($categoryId)
    {
        $categoryModel = $this->categoryFactory->create();
        $this->brandCategoryResource->load($categoryModel, $categoryId);
        if (!$categoryModel->getId()) {
            throw new NoSuchEntityException(
                __("The brand category that was requested doesn't exist. Verify the category and try again.")
            );
        }

        return $categoryModel;
    }

    /**
     * @inheritdoc
     */
    public function addCategory($category)
    {
        $category->setUrlKey($this->filter->translitUrl($category->getUrlKey()));
        $this->validateData($category);
        if ($category->getId()) {
            $categoryModel = $this->getCategoryById($category->getId());
        } else {
            $categoryModel = $this->categoryFactory->create();
        }
        $categoryModel->addData(Helper::jsonDecode(Helper::jsonEncode($category)));

        try {
            $this->brandCategoryResource->save($categoryModel);
        } catch (Exception $e) {
            throw new StateException(__('The brand category can\'t be saved.'));
        }

        return $categoryModel;
    }

    /**
     * @inheritdoc
     */
    public function updateCategory($categoryId, $category)
    {
        if (empty($categoryId)) {
            throw new InputException(__('Invalid option id %1', $categoryId));
        }
        $this->validateData($category);
        $categoryModel = $this->getCategoryById($categoryId);
        $categoryModel->addData(Helper::jsonDecode(Helper::jsonEncode($category)));
        try {
            $this->brandCategoryResource->save($categoryModel);
        } catch (Exception $e) {
            throw new StateException(__('The brand category can\'t be updated.'));
        }

        return $categoryModel;
    }

    /**
     * @inheritdoc
     */
    public function deleteCategory($categoryId)
    {
        if (empty($categoryId)) {
            throw new InputException(__('Invalid option id %1', $categoryId));
        }
        /** @var Category $categoryModel */
        $categoryModel = $this->categoryFactory->create();
        $this->brandCategoryResource->load($categoryModel, $categoryId);
        if (!$categoryModel->getId()) {
            throw new NoSuchEntityException(__('The "%1" brand category doesn\'t exist.', $categoryId));
        }
        try {
            $this->brandCategoryResource->delete($categoryModel);
        } catch (Exception $e) {
            throw new StateException(__('The brand category can\'t be delete.'));
        }

        return true;
    }

    /**
     * @param BrandCategoryInterface $category
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function validateData($category)
    {
        if (!$category->getName()) {
            throw new InputException(__('Missing category name.'));
        }

        if ($urlKey = $category->getUrlKey()) {
            /** @var Collection $pages */
            $pages = $this->categoryFactory->create()->getCollection()
                ->addFieldToFilter('url_key', $urlKey);
            if ($pages->getSize()) {
                if ($category->getId()) {
                    foreach ($pages as $page) {
                        if ((int) $page->getId() !== $category->getId()) {
                            throw new NoSuchEntityException(__('The url key has been used.', $urlKey));
                        }
                    }
                } else {
                    throw new NoSuchEntityException(__('The url key has been used.', $urlKey));
                }
            }
        } else {
            throw new NoSuchEntityException(__('Missing category url key'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getBrandConfigs($storeId = null)
    {
        if (!$this->helper->isEnabled()) {
            throw new InputException(__('Module Shop By Brand is disabled'));
        }
        $brandConfig = $this->helper->getConfigValue(Helper::CONFIG_MODULE_PATH, $storeId);

        $general = new General();
        $general->setIsEnabled($brandConfig['general']['enabled']);
        $general->setBrandAttribute($brandConfig['general']['attribute']);
        $general->setBrandRoute($brandConfig['general']['route']);
        $general->setBrandLinkTitle($brandConfig['general']['link_title']);
        $general->setShowBrandLinkIn($brandConfig['general']['show_position']);
        $general->setShowBrandInCategoryMenu($brandConfig['general']['show_dropdown']);
        $general->setWhatToShow($brandConfig['general']['show_brand_menu']);
        $general->setBrandMenuGridLayout($brandConfig['general']['grid_columns']);
        $general->setMaximumBrandsToShow($brandConfig['general']['limit_brands']);
        $general->setShowBrandsWithoutProductsOnMenu($brandConfig['general']['show_brands_without_products']);
        $general->setShowBrandInfoOnProductListingPage($brandConfig['general']['show_brand_info_in_listing']);
        $general->setShowBrandInfoInProductPage($brandConfig['general']['show_brand_info']);
        $general->setShowBrandInfoInProductAdminGrid($brandConfig['general']['show_brand_info_in_admin']);
        $general->setBrandLogoWidthInProductPage($brandConfig['general']['logo_width_on_product_page']);
        $general->setBrandLogoHeightInProductPage($brandConfig['general']['logo_height_on_product_page']);

        $brandsPageSettings = new BrandsPageSettings();
        $brandsPageSettings->setBrandListName($brandConfig['brandpage']['name']);
        $brandsPageSettings->setStyleOfBrandListPage($brandConfig['brandpage']['brandlist_style']);
        $brandsPageSettings->setDisplayOption($brandConfig['brandpage']['display']);
        $brandsPageSettings->setBrandLogoWidth($brandConfig['brandpage']['brand_logo_width']);
        $brandsPageSettings->setBrandLogoHeight($brandConfig['brandpage']['brand_logo_height']);
        $brandsPageSettings->setStyleColor($brandConfig['brandpage']['color']);
        $brandsPageSettings->setShowBrandShortDescription($brandConfig['brandpage']['show_description']);
        $brandsPageSettings->setShowBrandsWithoutProducts($brandConfig['brandpage']['show_brands_without_products']);
        $brandsPageSettings->setShowBrandProductQuantity($brandConfig['brandpage']['show_product_qty']);
        $brandsPageSettings->setShowBrandQuickViewPopup($brandConfig['brandpage']['show_quick_view']);
        $brandsPageSettings->setCustomCss($brandConfig['brandpage']['custom_css']);
        $brandsPageSettings->setShowBrandCategoriesFilter(
            $brandConfig['brandpage']['brand_filter']['enabled_cat_filter']
        );
        $brandsPageSettings->setShowBrandAlphabetFilter(
            $brandConfig['brandpage']['brand_filter']['enabled_alpha_filter']
        );
        $brandsPageSettings->setBrandFilterAlphabet($brandConfig['brandpage']['brand_filter']['alpha_bet']);
        $brandsPageSettings->setBrandFilterCharacterSet($brandConfig['brandpage']['brand_filter']['encode_key']);
        $brandsPageSettings->setShowBrandSearchBlock($brandConfig['brandpage']['search']['enable']);
        $brandsPageSettings->setBrandSearchMinChars($brandConfig['brandpage']['search']['min_search_chars']);
        $brandsPageSettings->setBrandSearchNumberOfSearchResult(
            $brandConfig['brandpage']['search']['max_query_results']
        );
        $brandsPageSettings->setBrandSearchShowThumbnailImage($brandConfig['brandpage']['search']['visible_images']);
        $brandsPageSettings->setShowFeaturedBrands($brandConfig['brandpage']['feature']['enable']);
        $brandsPageSettings->setFeaturedBrandTitle($brandConfig['brandpage']['feature']['title']);
        $brandsPageSettings->setDisplayFeaturedBrandsStyle($brandConfig['brandpage']['feature']['style']);
        $brandsPageSettings->setDisplayInformationFeaturedBrands($brandConfig['brandpage']['feature']['display']);
        $brandsPageSettings->setShowRelatedProducts($brandConfig['brandpage']['related_products']['enabled']);
        $brandsPageSettings->setBrandRelatedTitle($brandConfig['brandpage']['related_products']['title']);
        $brandsPageSettings->setBrandRelatedLimit($brandConfig['brandpage']['related_products']['limit_product']);

        $brandInfo = new BrandInfo();
        $brandInfo->setDefaultImage($brandConfig['brandview']['default_image']);
        $brandInfo->setDefaultBlock($brandConfig['brandview']['default_block']);
        $brandInfo->setShowBrandImageOnBrandPage($brandConfig['brandview']['show_image']);
        $brandInfo->setShowBrandDescriptionOnBrandPage($brandConfig['brandview']['show_description']);
        $brandInfo->setShowBrandStaticBlockOnBrandPage($brandConfig['brandview']['show_block']);

        $sidebar = new Sidebar();
        $sidebar->setShowFeaturedBrands($brandConfig['sidebar']['feature']['enable']);
        $sidebar->setFeaturedBrandTitle($brandConfig['sidebar']['feature']['title']);
        $sidebar->setFeaturedBrandShowTitle($brandConfig['sidebar']['feature']['show_title']);
        $sidebar->setShowBrandThumbnail($brandConfig['sidebar']['brand_thumbnail']['enable']);
        $sidebar->setBrandThumbnailTitle($brandConfig['sidebar']['brand_thumbnail']['title']);
        $sidebar->setBrandsQtyLimit($brandConfig['sidebar']['brand_thumbnail']['limit_brands']);
        $sidebar->setShowCategoryBrand($brandConfig['sidebar']['category_brand']['enable']);
        $sidebar->setCategoryBrandTitle($brandConfig['sidebar']['category_brand']['title']);
        $sidebar->setCategoryQtyLimit($brandConfig['sidebar']['category_brand']['limit_categories']);
        $sidebar->setShowBrandCategoryQuantity($brandConfig['sidebar']['category_brand']['show_brand_qty']);

        $seo = new Seo();
        $seo->setAddNoindexToPaginationPages($brandConfig['brand_seo']['seo_pages']);

        $this->brandConfig
            ->setGeneral($general)
            ->setBrandsPageSettings($brandsPageSettings)
            ->setBrandInfo($brandInfo)
            ->setSidebar($sidebar)
            ->setSeo($seo);

        return $this->brandConfig;
    }
}
