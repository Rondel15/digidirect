<?php
namespace Digidirect\Digi\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Class Categories
 * @package Digidirect\Digi\Model\Source
 */
class Categories extends AbstractSource implements OptionSourceInterface
{
    /**
     * @var CategoryCollectionFactory
     */
    private $categoryCollectionFactory;
    /**
     * @var StoreInterface
     */
    private $storeManager;

    /**
     * Categories constructor.
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param StoreInterface $storeManager
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        StoreInterface $storeManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAllOptions()
    {
        $options = [];

        $categories = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->setOrder('name', 'ASC')
            ->setStore($this->storeManager->getStore());

        foreach ($categories as $item) {
            $options[] = [
                'value' => $item->getId(),
                'label' => $item->getName(),
            ];
        }
        return $options;
    }
}
