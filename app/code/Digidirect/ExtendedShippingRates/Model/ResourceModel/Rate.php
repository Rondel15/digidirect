<?php
namespace Digidirect\ExtendedShippingRates\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Digidirect\ExtendedShippingRates\Model\Carrier as CarrierModel;
use Digidirect\ExtendedShippingRates\Api\Data\RateInterface;

class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * Rate constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        $connectionName = null
    ) {
        $this->string = $string;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CarrierModel::RATE_TABLE_NAME, RateInterface::RATE_ID);
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return $this
     */
    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        parent::load($object, $value, $field);
        $object->setData('skip_resource_after_load', true);
        $object->afterLoad();
        $object->unsetData('skip_resource_after_load');
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function delete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::delete($object);
        $object->afterDelete();
        return $this;
    }
}
