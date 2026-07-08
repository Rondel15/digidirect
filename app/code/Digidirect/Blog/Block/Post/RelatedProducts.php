<?php
namespace Digidirect\Blog\Block\Post;

use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\Post;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Module\Manager;
use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Cms\Model\Page;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Class RelatedProducts
 */
class RelatedProducts extends AbstractProduct implements IdentityInterface
{
    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var Visibility
     */
    protected $catalogProductVisibility;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var array
     */
    protected $productCollection = [];

    /**
     * RelatedProducts constructor.
     * @param Context $context
     * @param Manager $moduleManager
     * @param Visibility $catalogProductVisibility
     * @param Data $dataHelper
     * @param PostRepositoryInterface $postRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Manager $moduleManager,
        Visibility $catalogProductVisibility,
        Data $dataHelper,
        PostRepositoryInterface $postRepository,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->moduleManager = $moduleManager;
        $this->dataHelper = $dataHelper;
        $this->postRepository = $postRepository;
        $this->catalogProductVisibility = $catalogProductVisibility;
    }
    
    /**
     * @return $this
     */
    protected function prepareCollection()
    {
        $post = $this->getPost();
        $this->productCollection = $this->postRepository
            ->getRelatedProducts($post->getId())
            ->addAttributeToSelect('required_options');

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->productCollection);
        }
        $this->productCollection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $productsNumber = $this->dataHelper->getRelatedSettingsConfig('related_products/number_of_products');
        $this->productCollection->setPageSize($productsNumber);
        $this->productCollection->load();

        foreach ($this->productCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function displayProducts()
    {
        return $this->dataHelper->getRelatedSettingsConfig('related_products/enabled');
    }

    /**
     * @return bool
     */
    public function displayCart()
    {
        return (bool)$this->dataHelper->getRelatedSettingsConfig('related_products/show_addtocart');
    }

    /**
     * @return bool
     */
    public function displayWishList()
    {
        return (bool)$this->dataHelper->getRelatedSettingsConfig('related_products/show_whishlist_icon');
    }

    /**
     * @return bool
     */
    public function displayCompare()
    {
        return (bool)$this->dataHelper->getRelatedSettingsConfig('related_products/show_compare_icon');
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        if (empty($this->productCollection)) {
            $this->prepareCollection();
        }
        return $this->productCollection;
    }

    /**
     * @return Post
     */
    public function getPost()
    {
        return $this->_coreRegistry->registry(PostInterface::CURRENT_ITEM);
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [Page::CACHE_TAG . '_relatedproducts_' . $this->getPost()->getId()];
    }

    /**
     * Find out if some products can be easy added to cart
     *
     * @return bool
     */
    public function canItemsAddToCart()
    {
        foreach ($this->getItems() as $item) {
            if (!$item->isComposite() && $item->isSaleable() && !$item->getRequiredOptions()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool|int|null
     */
    public function getCacheLifetime()
    {
        $cacheLifetime = parent::getCacheLifetime();
        if (!$cacheLifetime) {
            $cacheLifetime = 86400;
        }

        return $cacheLifetime;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKey = parent::getCacheKeyInfo();
        $cacheKey['nil'] = $this->getNameInLayout();
        $cacheKey['request_params'] = json_encode($this->getRequest()->getParams());
        return $cacheKey;
    }
}
