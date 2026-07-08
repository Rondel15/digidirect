<?php

namespace Digidirect\Feed\Model\Dynamic;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * @method \Digidirect\Feed\Model\ResourceModel\Dynamic\Attribute getResource()
 */
class Attribute extends AbstractModel
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'digidirect_feed_dynamic_attribute';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'attribute';

    /**
     * @var Serialize
     */
    protected $serializer;

    /**
     * @var Attribute\Validator
     */
    protected $validator;

    /**
     * Attribute constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Serialize $serializer
     * @param Attribute\Validator $validator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Serialize $serializer,
        Attribute\Validator $validator,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Feed\Model\ResourceModel\Dynamic\Attribute');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param \Digidirect\Feed\Export\Resolver\ProductResolver $resolver
     * @return string
     */
    public function getValue($product, $resolver)
    {
        foreach ($this->getConditions() as $condition) {
            $valid = true;
            if (isset($condition['statement'])) {
                foreach ($condition['statement'] as $statement) {
                    $attrValue = $product->getData($statement['attribute']);

                    if (is_scalar($attrValue)) {
                        $attrValue = trim($attrValue);
                    }

                    $this->validator->setOperator($statement['operator'])
                        ->setValue($statement['value'])
                        ->setData('value_parsed', $statement['value']);

                    if (in_array($statement['operator'], ['()', '!()'])) {
                        $attrValue = explode(',', $attrValue);
                        $attrValue = array_map('trim', $attrValue);
                    }

                    if (!$this->validator->validateAttribute($attrValue)) {
                        $valid = false;
                    }
                }
            }

            if ($valid) {
                if ($condition['result']['type'] == 'pattern') {
                    return $condition['result']['value'];
                } else {
                    return $resolver->resolve($product, $condition['result']['value']);
                }
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        if (!$this->hasData('conditions')) {
            $conditionsSerialized = $this->_getData('conditions_serialized');
            $conditions = $conditionsSerialized ? $this->serializer->unserialize($conditionsSerialized) : [];
            $this->setData('conditions', $conditions);
        }

        return $this->getData('conditions');
    }
}
