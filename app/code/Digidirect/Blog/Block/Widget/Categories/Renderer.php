<?php
namespace Digidirect\Blog\Block\Widget\Categories;

use Digidirect\Blog\Api\CategoryRepositoryInterface;
use Digidirect\Blog\Api\Data\CategoryInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\UrlModel;
use Digidirect\Blog\Model\Category;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

/**
 * Class Categories
 */
class Renderer extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Digidirect_Blog::widget/categories/item.phtml';
    
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var UrlModel
     */
    protected $urlModel;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * Renderer constructor.
     * @param Context $context
     * @param Data $dataHelper
     * @param UrlModel $urlModel
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        UrlModel $urlModel,
        CategoryRepositoryInterface $categoryRepository,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->urlModel = $urlModel;
        $this->categoryRepository = $categoryRepository;
        $this->registry = $registry;
    }
    
    /**
     * @param array $categories
     * @return string
     */
    public function render(array $categories)
    {
        $html = '';
        foreach ($categories as $category) {
            $html .= $this->getItemBlockHtml($category);
        }
        return $html;
    }

    /**
     * @param array $category
     * @return string
     */
    public function getItemBlockHtml(array $category)
    {
        $category = new DataObject($category);
        $this->setCategory($category);
        return $this->toHtml();
    }

    /**
     * @param string $urlKey
     * @return string
     */
    public function getCategoryUrl($urlKey)
    {
        return rtrim($this->urlModel->getCategoryUrl($urlKey), '/');
    }

    /**
     * @param int $categoryId
     * @return string
     */
    public function getCountPosts($categoryId)
    {
        return $this->categoryRepository->getCountPostsByCategoryId(
            $categoryId,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * @param int $categoryId
     * @return bool
     */
    public function isActive($categoryId)
    {
        /** @var Category|null $currentCategory */
        $currentCategory = $this->registry->registry(CategoryInterface::CURRENT_ITEM);
        return $currentCategory !== null && $currentCategory->getId() == $categoryId;
    }
}
