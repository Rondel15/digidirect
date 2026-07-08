<?php

namespace Digidirect\Feed\Model\ResourceModel\Dynamic;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Framework\Model\AbstractModel;

class Category extends AbstractDb
{
    /**
     * @var array
     */
    protected $_uniqueFields = [
        [
            'field' => 'code',
            'title' => 'Category mapping with the same code',
        ],
    ];

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * Category constructor.
     * @param Context $context
     * @param Serialize $serializer
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Serialize $serializer,
        CategoryCollectionFactory $categoryCollectionFactory,
        $connectionName = null
    ) {
        $this->serializer = $serializer;
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_feed_mapping_category', 'mapping_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->getData('mapping') && is_array($object->getData('mapping'))) {
            $object->setData('mapping_serialized', $this->serializer->serialize($object->getData('mapping')));
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getData('mapping_serialized')) {
            $object->setData('mapping', $this->serializer->unserialize($object->getData('mapping_serialized')));
        }

        return parent::_afterLoad($object);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCategoriesData()
    {
        $collection = $this->categoryCollectionFactory->create();

        $connection = $collection->getConnection();
        $select = $collection->getSelect();
        $select->reset(\Zend_Db_Select::COLUMNS)
            ->reset(\Zend_Db_Select::ORDER)
            ->columns(
                [
                    'entity_id',
                    \Magento\Catalog\Model\Category::KEY_PARENT_ID,
                    \Magento\Catalog\Model\Category::KEY_PATH,
                    \Magento\Catalog\Model\Category::KEY_LEVEL,
                    new \Zend_Db_Expr('ABS(children_count) as children_count'),
                ]
            )
            ->order(\Magento\Catalog\Model\Category::KEY_LEVEL . ' ' . \Zend_Db_Select::SQL_ASC)
            ->order(\Magento\Catalog\Model\Category::KEY_POSITION . ' ' . \Zend_Db_Select::SQL_ASC);

        $collection->addAttributeToSelect(\Magento\Catalog\Model\Category::KEY_NAME, 'left');

        $stmt = $connection->query($select);

        $data = [];
        while ($row = $stmt->fetch()) {
            $categoryId = $row['entity_id'];
            $parentId = $row[\Magento\Catalog\Model\Category::KEY_PARENT_ID];
            unset($row['entity_id']);
            unset($row[\Magento\Catalog\Model\Category::KEY_PARENT_ID]);
            $data[$parentId][$categoryId] = $row;
        }

        return $data;
    }
}
