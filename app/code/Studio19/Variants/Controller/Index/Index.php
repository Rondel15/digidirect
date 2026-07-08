<?php

namespace Studio19\Variants\Controller\Index;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Catalog\Api\CategoryRepositoryInterface;
use \Magento\Framework\Json\Helper\Data as JsonHelper;
use \Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\UrlInterface;
use \Magento\Catalog\Api\Data\ProductInterface;
use \Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Magento\Catalog\Helper\Image as ImageHelper;
use \Magento\Framework\Pricing\PriceCurrencyInterface;
use \Magento\Framework\AuthorizationInterface;
use Studio19\Variants\Api\Data\ProductVariantPagedResultInterfaceFactory;

/**
 * Product variants controller.
 */
class Index extends Action {

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     *
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     *
     */
    protected $jsonResultFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     *
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Image
     *
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     *
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     *
     */
    protected $authorisation;

    /**
     * Constructor.
     *
     * @param ResourceModel\Product\CollectionFactory $collectionFactory
     * @param ProductRepositoryInterface Repository for retrieving products.
     * @param ProductVariantPagedResultInterfaceFactory Repository for retrieving products.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Controller\Result $jsonResultFactory
     */
    public function __construct(
        Context $context,
        //AuthorizationInterface $authorisation,
        CollectionFactory $collectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        JsonFactory $jsonResultFactory,
        ImageHelper $imageHelper,
        PriceCurrencyInterface $pricingHelper
    ) {
        $this->collectionFactory = $collectionFactory;
        //$this->authorisation = $authorisation;
        $this->categoryRepository = $categoryRepository;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->imageHelper = $imageHelper;
        $this->pricingHelper = $pricingHelper;
        $this->urlBuilder = $context->getUrl();

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonResultFactory->create();

        // Check for properly authorised request.
        // if (!$this->authorisation->isAllowed(\Magento\Backend\App\AbstractAction::ADMIN_RESOURCE)) {
        //     $result->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_FORBIDDEN);
        //     return $result;
        // }

        $attributes = $this->getRequest()->getParam('attributes');

        if ($attributes)
            $attributes = explode(',', $attributes);
        else
            $attributes = array();

        $debug = $this->getRequest()->getParam('debug') === 'true';
        $top = $this->getRequest()->getParam('top');
        $skip = $this->getRequest()->getParam('skip');

        // Extra filtering parameters.
        $excludeLinked = $this->getRequest()->getParam('excludeLinked') === 'true';

        // Generous defaults, should the parameters not be provided.
        if (!$top)
            $top = 500;
        if (!$skip)
            $skip = 0;

        $collection = $this->collectionFactory->create()
            ->addAttributeToSelect(array_merge($attributes, ['name', 'price', 'final_price', 'description', 'short_description', 'manufacturer', 'image', 'small_image']))
            ->addAttributeToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE])
            ->addAttributeToFilter('status', ['eq' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED])
            ->addAttributeToFilter('type_id', ['in' => [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE]]);

        $select = $collection->getSelect();

        // Filter out simple products which are linked to a configurable/bundled product; these will be included as variants of the
        // parent product.

        if ($excludeLinked)
        {
            $select
                ->joinLeft(['link_table' => 'catalog_product_super_link'], 'link_table.product_id = e.entity_id', ['product_id'])
                ->where('link_table.product_id IS NULL');
        }

        $select->limit($top, $skip);
        $items = $collection
            ->addCategoryIds()
            ->load()
            ->getItems();

        $variants = array();

        foreach ($items as $product) {
            $this->prepareProductForResponse($product);
            $productData = $product->getData();

            $productOutput = [
                    'id' => $productData['entity_id'],
                    'title' => $productData['name'],
                    'link' => $productData['url'],
                    'image_link' => $productData['image_url'],
                    'description' => array_key_exists('description', $productData) ? $productData['description'] :
                                     (array_key_exists('short_description', $productData) ? $productData['short_description'] : null),
                    'brand' => $productData['manufacturer_value'],
                    'price' => $this->pricingHelper->format($productData['final_price_with_tax'], false),
                    'categories' => $productData['categories'],
                    'variants' => $productData['variants'],
                    'options' => $productData['custom_options'],
                ];

            foreach ($attributes as $attribute)
            {
                if ($attribute != '*')
                    $productOutput[$attribute] = array_key_exists($attribute, $productData) ? $productData[$attribute] : null;
            }

            // If $debug is true, add an extra property to dump the raw data out.
            if ($debug)
                $productOutput['raw_data'] = $productData;

            array_push($variants, (object)$productOutput);
        }

        // Prepare the paged response object
        $pagedResult = (object) [
            'count' => sizeof($items),
            'items' => $variants,
            'generator' => 'Studio19 Magento2 Extension 1.0.5',
        ];

        if ($top == sizeof($items))
            $pagedResult->nextPage = $this->urlBuilder->getUrl('*/*/*', ['top' => $top, 'skip' => $top + $skip]);

        $result->setData($pagedResult);

        return $result;
    }

    /**
     * Add special fields to product response
     *
     * @param ProductInterface $product
     */
    protected function prepareProductForResponse(ProductInterface $product)
    {
        $productData = $product->getData();
        $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
        $final_price2 = $product->getFinalPrice();
        $final_price3 = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        $productData['final_price_with_tax'] = $final_price3;//$product->getFinalPrice();
        $productData['image_url'] = $this->imageHelper->init($product, 'product_base_image')->constrainOnly(false)->keepAspectRatio(true)->keepFrame(false)->resize(500, 500)->getUrl();
        $productData['url'] = $product->getProductUrl();
        $productData['manufacturer_value'] = array_key_exists('manufacturer', $productData) ? $product->getAttributeText('manufacturer') : null;

        $categories = $product
            ->getCategoryCollection()
            ->addNameToResult();
        $productData['categories'] = array();

        foreach ($categories as $category) {
            $categoryData = $category->getData();

            // Convert path to human readable form (i.e. using names rather than ids).
            if ($categoryData['path']) {
                $string = '';

                foreach (explode('/', $categoryData['path']) as $pathId) {
                    $categoryInfo = $this->categoryRepository->get($pathId)->getData();

                    if ($categoryInfo) {
                        if (!$string)
                            $string = $categoryInfo['name'];
                        else
                            $string .= ' &gt; '.$categoryInfo['name'];
                    }
                }

                $categoryData['path'] = $string;
            }

            array_push($productData['categories'], $categoryData['path']);
        }

        // Depending on the product type, we may need to populate variants
        $productData['variants'] = array();

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $attributes = $product->getTypeInstance(true)
                ->getConfigurableAttributeCollection($product)
                ->load();

            $optionsByAttributeValues = array();
            $attributeCodes = array();

            // Cache access to the prices for each attribute value.
            foreach ($attributes as $attribute) {
                array_push($attributeCodes, $attribute->getProductAttribute()->getAttributeCode());
                $options = $attribute->getOptions();

                foreach ($options as $option)
                    $optionsByAttributeValues[$option['value_index']] = $option['label'];
            }

            $subProducts = $product->getTypeInstance(true)
                ->getUsedProductCollection($product)
                ->addAttributeToSelect(array_merge($attributeCodes, array('name', 'description', 'short_description', 'price', 'image')))
                ->load();

            foreach ($subProducts as $variant) {
                $variantData = $variant->getData();
                $attributeValues = array();
                $skip = false;

                // Collect attribute values for each appropriate attribute.
                foreach ($attributes as $attribute) {
                    $code = $attribute->getProductAttribute()->getAttributeCode();

                    // If the key does not exist, it probably has not been set on the products, and is thus misconfigured, so it should not be added to output.
                    // Update 16/1/18 -- Extra condition check since Power Golf is giving out of bounds exception on the 'name' => ... line below.
                    // Update 15/5/18 -- Add try/catch since I can't work out why the code is failing
                    if (isset($code, $variantData[$code], $optionsByAttributeValues, $optionsByAttributeValues[$variantData[$code]])) {
                        $attributeValues[$attribute->getData('label')] = array(
                            'id' => intval($variantData[$code]),
                            'name' => $optionsByAttributeValues[$variantData[$code]]
                        );
                    }
                    else
                        $skip = true;
                }

                $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $final_price2 = $product->getFinalPrice();
                $final_price3 = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
                if (!$skip) {
                    array_push($productData['variants'], (object)[
                            'id' => $variantData['entity_id'],
                            'title' => $variantData['name'],
                            'image_link' => $this->imageHelper->init($variant, 'product_base_image')->constrainOnly(false)->keepAspectRatio(true)->keepFrame(false)->resize(500, 500)->getUrl(),
                            'description' => array_key_exists('description', $variantData) ? $variantData['description'] :
                                            (array_key_exists('short_description', $variantData) ? $variantData['short_description'] : null),
                            'attributes' => $attributeValues,
                            'price' => $final_price3,
                        ]);
                }
            }
        }

        // Find custom options configured for this product
        $customOptions = $product->getProductOptionsCollection();
        $productData['custom_options'] = array();

        foreach ($customOptions as $option) {
            $optionData = $option->getData();
            $values = $option->getValues();

            // We should only be concerned with this option if there are multiple options provided.
            // In the case of date or text fields/areas, these won't be necessary for S19.
            if (is_array($values)) {
                foreach ($values as $value) {
                    $valueData = $value->getData();
                    array_push($productData['custom_options'], (object)[
                        'id' => $valueData['option_type_id'],
                        'name' => $valueData['title'],
                        'category' => $optionData['title'],
                        'price' => floatval($valueData['price']),
                    ]);
                }
            }
        }

        $product->addData($productData);
    }
}
