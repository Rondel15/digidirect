<?php

namespace Digidirect\Blog\Helper;

use Digidirect\Blog\Api\CategoryRepositoryInterfaceFactory;
use Digidirect\Blog\Model\Config\Provider\Status;
use Magento\Framework\App\Helper\Context;
use Digidirect\Blog\Model\CategoryRepository;

class Category extends Data
{
    /**
     * @var CategoryRepositoryInterfaceFactory
     */
    protected $categoryRepositoryFactory;

    /**
     * @var array
     */
    protected $categories = [];

    /**
     * Category constructor.
     *
     * @param Context $context
     * @param CategoryRepositoryInterfaceFactory $categoryRepositoryInterfaceFactory
     */
    public function __construct
    (
        Context $context,
        CategoryRepositoryInterfaceFactory $categoryRepositoryInterfaceFactory
    ) {
        parent::__construct($context);
        $this->categoryRepositoryFactory = $categoryRepositoryInterfaceFactory;
    }

    /**
     * @return void
     */
    public function prepareCategoriesUrls()
    {
        if (empty($this->categories)) {
            /**
             * @var $categoryRepository CategoryRepository
             */
            $categoryRepository = $this->categoryRepositoryFactory->create();
            $categories = $categoryRepository->getCategories(Status::STATUS_ENABLED);
            foreach ($categories as $category) {
                $this->categories[$category->getId()] = $category;
            }
        }
    }

    /**
     * @param int $categoryId
     * @return mixed|null
     */
    public function getCategory($categoryId)
    {
        return $this->categories[$categoryId] ?? null;
    }
}
