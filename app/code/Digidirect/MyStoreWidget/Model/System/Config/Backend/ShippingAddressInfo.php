<?php
namespace Digidirect\MyStoreWidget\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

/**
 * Backend for serialized array data
 */
class ShippingAddressInfo extends Value
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'digidirect_my_store_widget_config';

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * ShippingAddressInfo constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param Serializer $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Serializer $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
        $this->serializer = $serializer;
    }

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $this->setValue($this->convertValueToArray($value));
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = [];
        $attributes = (array)$this->getValue();
        if (!empty($attributes)) {
            unset($attributes['__empty']);
            foreach ($attributes as $attribute) {
                $value[$attribute['shipping_field'] . '_' . $attribute['entity_attribute']] = $attribute;
            }
            ksort($value);
        }
        $value = $this->serializer->serialize($value);
        $this->setValue($value);
    }

    /**
     * @param string $value
     * @return array|bool|float|int|null|string
     */
    public function convertValueToArray($value)
    {
        try {
            $value = $this->serializer->unserialize($value);
        } catch (\Exception $e) {
            $value = [];
        }
        return $value;
    }
}
