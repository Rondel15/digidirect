<?php

namespace Digidirect\Feed\Model\ResourceModel\Dynamic;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Serialize;

class Attribute extends AbstractDb
{
    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @var array
     */
    protected $_uniqueFields = [
        [
            'field' => 'code',
            'title' => 'Dynamic attribute with the same code',
        ],
    ];

    /**
     * Attribute constructor.
     * @param Context $context
     * @param Serialize $serializer
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Serialize $serializer,
        $connectionName = null
    ) {
        $this->serializer = $serializer;

        parent::__construct($context, $connectionName);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('digidirect_feed_dynamic_attribute', 'attribute_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if ($object->getData('conditions') && is_array($object->getData('conditions'))) {
            $object->setData('conditions_serialized', $this->serializer->serialize($object->getData('conditions')));
        }

        return parent::_beforeSave($object);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getData('conditions_serialized')) {
            $object->setData('conditions', $this->serializer->unserialize($object->getData('conditions_serialized')));
        }

        return parent::_afterLoad($object);
    }
}
