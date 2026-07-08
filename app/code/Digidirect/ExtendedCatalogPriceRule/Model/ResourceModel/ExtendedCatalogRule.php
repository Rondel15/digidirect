<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ExtendedCatalogRule
 * @package Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel
 */
class ExtendedCatalogRule extends AbstractDb
{
    const EXTENDED_CATALOG_RULE_TABLE = 'digidirect_extended_catalog_price_rule';

    /**
     * @var \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::EXTENDED_CATALOG_RULE_TABLE, ExtendedCatalogRuleInterface::ID);
    }

    /**
     * ExtendedCatalogRule constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * @param array $productIds
     * @param string $ruleActionCode
     * @param int $customerGroupId
     * @param int $websiteId
     * @param string|int $date
     * @param bool $all
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getExtendedRuleForProducts(
        array $productIds,
        $ruleActionCode,
        $customerGroupId = null,
        $websiteId = null,
        $date = null,
        $all = true
    ) {
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }

        $select = $connection->select()
            ->from(['catalogrule_product' => $this->getTable('catalogrule_product')])
            ->where('product_id IN (?)', $productIds)
            ->where('action_operator = ?', $ruleActionCode)
            ->order('catalogrule_product.sort_order');

        if (null !== $customerGroupId) {
            $select->where('customer_group_id = ?', $customerGroupId);
        }
        if (null !== $websiteId) {
            $select->where('website_id = ?', $websiteId);
        }
        if (null !== $date) {
            $select->where('from_time = 0 or from_time < ?', $date)
                ->where('to_time = 0 or to_time > ?', $date);
        }

        $select->joinLeft(
            ['extended_rule' => $this->getMainTable()],
            new \Zend_Db_Expr(
                'catalogrule_product.rule_id = extended_rule.' . ExtendedCatalogRuleInterface::RULE_ID
            )
        );

        return $all ? $connection->fetchAll($select) : $connection->fetchRow($select);
    }

    /**
     * @param int $ruleId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByCatalogRuleId($ruleId)
    {
        $connection = $this->getConnection();
        return $connection->delete(
            $this->getMainTable(),
            [ExtendedCatalogRuleInterface::RULE_ID .'=?' => $ruleId]
        );
    }
}
