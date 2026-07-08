<?php

namespace Digidirect\Feed\Model\Dynamic;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * @method \Digidirect\Feed\Model\ResourceModel\Dynamic\Category getResource()
 */
class Category extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_dynamic_category';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'category';

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var array
     */
    static protected $categoriesData = null;

    /**
     * Category constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serialize $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\ResourceModel\Dynamic\Category');
    }

    /**
     * Current mapping
     *
     * @return array
     */
    public function getMapping()
    {
        if ($this->mapping == null) {
            if (!$this->hasData('mapping')) {
                $mappingSerialized = $this->_getData('mapping_serialized');
                $mapping = $mappingSerialized ? $this->serializer->unserialize($mappingSerialized) : [];
                $this->setData('mapping', $mapping);
            }
            if (self::$categoriesData === null) {
                self::$categoriesData = $this->getResource()->getCategoriesData();
            }
            $this->mapping = [];
            $this->buildMapping();
        }

        return $this->mapping;
    }

    /**
     * Build mapping
     *
     * @param int $parentId
     * @return $this
     */
    protected function buildMapping($parentId = 0)
    {
        $userMapping = $this->getData('mapping');

        foreach (self::$categoriesData[$parentId] as $categoryId => $categoryData) {
            if (!empty($categoryData['name'])) {
                $this->mapping[$categoryId] = [
                    'category_id' => $categoryId,
                    'name' => $categoryData['name'],
                    'map' => isset($userMapping[$categoryId]) ? $userMapping[$categoryId] : '',
                    'level' => $categoryData[\Magento\Catalog\Model\Category::KEY_LEVEL],
                    'path' => $categoryData[\Magento\Catalog\Model\Category::KEY_PATH],
                    'parent_id' => $parentId,
                    'has_childs' => $categoryData['children_count'] > 0,
                ];
            }

            if (isset(self::$categoriesData[$categoryId])) {
                $this->buildMapping($categoryId);
            }
        }

        return $this;
    }

    /**
     * @param int $categoryId
     * @return string
     */
    protected function getDirectMappingValue($categoryId)
    {
        $this->getMapping();
        return !empty($this->mapping[$categoryId]['map']) ? $this->mapping[$categoryId]['map'] : '';
    }

    /**
     * @param int $categoryId
     * @return string
     */
    protected function getParentMappingValue($categoryId)
    {
        $this->getMapping();
        if (!empty($this->mapping[$categoryId]['path'])) {
            $path = explode('/', $this->mapping[$categoryId]['path']);
            $path = array_reverse($path);
            foreach ($path as $id) {
                if (isset($this->mapping[$id]) && !empty($this->mapping[$id]['map'])) {
                    return $this->mapping[$id]['map'];
                }
            }
        }
        return '';
    }
    
    /**
     * Return mapping value by category id
     *
     * @param int|array $categories
     * @return string
     */
    public function getMappingValue($categories)
    {
        if (!is_array($categories)) {
            $categories = [$categories];
        }

        foreach ($categories as $categoryId) {
            if ($result = $this->getDirectMappingValue($categoryId)) {
                return $result;
            }
        }

        foreach ($categories as $categoryId) {
            if ($result = $this->getParentMappingValue($categoryId)) {
                return $result;
            }
        }

        return '';
    }
}
