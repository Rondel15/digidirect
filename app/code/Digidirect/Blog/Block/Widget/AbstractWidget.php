<?php

namespace Digidirect\Blog\Block\Widget;

use Digidirect\Blog\Api\CategoryRepositoryInterface;
use Digidirect\Blog\Api\Data\PostInterface;
use Digidirect\Blog\Api\PostRepositoryInterface;
use Digidirect\Blog\Helper\Data;
use Digidirect\Blog\Model\UrlModel;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Digidirect\Blog\Helper\Category;

abstract class AbstractWidget extends Template
{
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
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * @var Category
     */
    protected $categoryHelper;

    /**
     * AbstractWidget constructor.
     *
     * @param Context $context
     * @param Data $dataHelper
     * @param UrlModel $urlModel
     * @param CategoryRepositoryInterface $categoryRepository
     * @param PostRepositoryInterface $postRepository
     * @param Category $category
     * @param array|[] $data
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        UrlModel $urlModel,
        CategoryRepositoryInterface $categoryRepository,
        PostRepositoryInterface $postRepository,
        Category $category,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->urlModel = $urlModel;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
        $this->categoryHelper = $category;
    }

    /**
     * @param PostInterface $post
     * @return \Digidirect\Blog\Model\Category[]
     */
    public function getCategoriesTitlesAsArray(PostInterface $post)
    {
        $categoryIds = $post->getData('category_id');
        $categories = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryHelper->getCategory($categoryId);
            if (!empty($category)) {
                $categories[] = $this->categoryHelper->getCategory($categoryId);
            }
        }

        return $categories;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->dataHelper->isModuleEnabled()) {
            return '';
        }

        return parent::_toHtml();
    }
}
