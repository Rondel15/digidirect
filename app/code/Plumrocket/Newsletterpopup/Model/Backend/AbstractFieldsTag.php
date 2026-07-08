<?php
/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

namespace Plumrocket\Newsletterpopup\Model\Backend;

abstract class AbstractFieldsTag extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Plumrocket\Newsletterpopup\Helper\Data
     */
    private $dataHelper;

    /**
     * AbstractFieldsTag constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Plumrocket\Newsletterpopup\Helper\Data $dataHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Plumrocket\Newsletterpopup\Helper\Data $dataHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    abstract public function getFields();

    /**
     * @return array
     */
    private function getPreparedFields()
    {
        $result = [];
        $systemItems = $this->dataHelper->getPopupFormFields(0, false);

        foreach ($this->getFields() as $key => $value) {
            $result[$key] = [
                'name' => $key,
                'label' => $value,
                'orig_label' => isset($systemItems[$key]) ? $systemItems[$key]->getData('label') : ucfirst($key),
            ];
        }

        return $result;
    }

    /**
     * @return $this
     */
    public function afterLoad()
    {
        $value = $this->parseValue($this->getValue());
        $this->setValue($value);

        parent::afterLoad();

        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();

        $newValue = [];
        $values = $this->getValue();
        $formFields = $this->getPreparedFields();

        if (is_array($values)) {
            foreach ($values as $name => $value) {
                if (array_key_exists($name, $formFields)) {
                    $newValue[$name] = isset($value['label']) ? (string)$value['label'] : '';
                }
            }
        }

        $this->setValue(json_encode($newValue));

        return $this;
    }

    /**
     * @param $value
     * @return array
     */
    public function parseValue($value)
    {
        $formFields = $this->getPreparedFields();
        $values = json_decode((string)$value, true);

        if (is_array($values)) {
            foreach ($values as $name => $value) {
                $formFields[$name]['label'] = ! empty($value) ? (string)$value : $formFields[$name]['label'];
            }
        }

        return $formFields;
    }
}
