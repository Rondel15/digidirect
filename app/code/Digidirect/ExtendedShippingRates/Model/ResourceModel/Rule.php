<?php
namespace Digidirect\ExtendedShippingRates\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Digidirect\ExtendedShippingRates\Model\Rule as RuleModel;
use Digidirect\ExtendedShippingRates\Api\Data\RuleInterface;

/**
 * Sales Rule resource model
 */
class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table' => \Digidirect\ExtendedShippingRates\Model\Rule::STORE_TABLE_NAME,
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'store_id',
        ],
        'customer_group' => [
            'associations_table' => \Digidirect\ExtendedShippingRates\Model\Rule::CUSTOMER_GROUP_TABLE_NAME,
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id',
        ],
    ];

    /**
     * @var array
     */
    protected $customerGroupIds = [];

    /**
     * @var array
     */
    protected $storeIds = [];

    /**
     * Serializable field: amounts
     *
     * @var array
     */
    protected $_serializableFields = [
        'amount' => [null, []],
        'action_type' => [null, []],
        'shipping_methods' => [null, []],
        'disabled_shipping_methods' => [null, []],
        'enabled_shipping_methods' => [null, []],
        'used_alt_title_shipping_methods' => [null, []],
        'used_alt_code_shipping_methods' => [null, []]
    ];

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(RuleModel::RULE_TABLE_NAME, RuleInterface::RULE_ID);
    }

    /**
     * Add customer group ids and store ids to rule data after load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $this->loadCustomerGroupIds($object);
        $this->loadStoreIds($object);

        parent::_afterLoad($object);
        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return void
     */
    public function loadCustomerGroupIds(AbstractModel $object)
    {
        if (!$this->customerGroupIds) {
            $this->customerGroupIds = (array)$this->getCustomerGroupIds($object->getId());
        }
        $object->setData('customer_group_ids', $this->customerGroupIds);
    }

    /**
     * @param AbstractModel $object
     * @return void
     */
    public function loadStoreIds(AbstractModel $object)
    {
        $this->storeIds = (array)$this->getStoreIds($object->getId());
        $object->setData('store_ids', $this->storeIds);
    }

    /**
     * Retrieve store ids of specified rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'store');
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    public function _beforeSave(AbstractModel $object)
    {
        parent::_beforeSave($object);
        return $this;
    }

    /**
     * Bind shipping rule to customer group(s) and store(s).
     * Save rule's associated store labels.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreIds()) {
            $storeIds = $object->getStoreIds();
            if (!is_array($storeIds)) {
                $storeIds = explode(',', (string)$storeIds);
            }
            $this->bindRuleToEntity($object->getId(), $storeIds, 'store');
        }

        if ($object->hasCustomerGroupIds()) {
            $customerGroupIds = $object->getCustomerGroupIds();
            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string)$customerGroupIds);
            }
            $this->bindRuleToEntity($object->getId(), $customerGroupIds, 'customer_group');
        }

        return parent::_afterSave($object);
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

    /**
     * @return array
     */
    public function getSerializedFields()
    {
        return $this->_serializableFields;
    }
}
