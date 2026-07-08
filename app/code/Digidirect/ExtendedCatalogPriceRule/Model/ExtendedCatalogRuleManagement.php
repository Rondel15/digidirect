<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\ExtendedCatalogRuleInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Api\ExtendedCatalogRuleManagementInterface;
use Digidirect\ExtendedCatalogPriceRule\Helper\Data;
use Digidirect\ExtendedCatalogPriceRule\Model\ExtendedCatalogRuleFactory as ExtendedCatalogRuleFactory;
use Digidirect\ExtendedCatalogPriceRule\Model\ResourceModel\ExtendedCatalogRule as ExtendedCatalogRuleResource;
use Psr\Log\LoggerInterface;

/**
 * Class ExtendedCatalogRuleManagement
 * @package Digidirect\ExtendedCatalogPriceRule\Model
 */
class ExtendedCatalogRuleManagement implements ExtendedCatalogRuleManagementInterface
{
    /**
     * @var ExtendedCatalogRuleResource
     */
    protected $ruleResource;

    /**
     * @var \Digidirect\ExtendedCatalogPriceRule\Model\ExtendedCatalogRuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * ExtendedCatalogRuleManagement constructor.
     * @param ExtendedCatalogRuleResource $ruleResource
     * @param \Digidirect\ExtendedCatalogPriceRule\Model\ExtendedCatalogRuleFactory $ruleFactory
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExtendedCatalogRuleResource $ruleResource,
        ExtendedCatalogRuleFactory $ruleFactory,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param array $productIds
     * @param string $actionCode
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     * @param string|null $date
     * @param bool $formatActionAmount
     * @return array
     */
    public function getExtendedRulesGroupedByProductIds(
        array $productIds,
        $actionCode,
        $websiteId = null,
        $customerGroupId = null,
        $date = null,
        $formatActionAmount = false
    ) {
        $rulesData = $this->getExtendedRulesDataByProductIds(
            $productIds,
            $actionCode,
            $websiteId,
            $customerGroupId,
            $date
        );
        if (empty($rulesData)) {
            return [];
        }
        $result = array_fill_keys($productIds, []);

        foreach ($rulesData as $row) {
            if ($formatActionAmount && ($discount = $row['action_amount'] ?? null)) {
                $row['action_amount'] = $this->helper->formatDiscountAmount($discount);
            }
            if (!empty($description = $row[RuleDisplayMessageInterface::PDP_DESCRIPTION])) {
                $row[RuleDisplayMessageInterface::PDP_DESCRIPTION] = $this->helper->prepareContent($description);
            }
            $productId = $row['product_id'];
            $result[$productId][$row['rule_id']] = $row;
        }
        return $result;
    }

    /**
     * @param array $productIds
     * @param string $actionCode
     * @param int|null $websiteId
     * @param int|null $customerGroupId
     * @param string|null $date
     * @return array
     */
    public function getExtendedRulesDataByProductIds(
        array $productIds,
        $actionCode,
        $websiteId = null,
        $customerGroupId = null,
        $date = null
    ) {
        try {
            $rules = $this->ruleResource->getExtendedRuleForProducts(
                $productIds,
                $actionCode,
                $customerGroupId,
                $websiteId,
                $date
            );
        } catch (\Exception $exception) {
            $this->logger->error(
                __(
                    'Can\'t fetch a Catalog rule (action code is [%1]) for products. Error: %2',
                    $actionCode,
                    $exception->getMessage()
                )
            );
            return [];
        }
        return $rules;
    }

    /**
     * @param int $ruleId
     * @return ExtendedCatalogRule
     */
    public function loadByCatalogRuleId($ruleId)
    {
        $extendedRule = $this->ruleFactory->create();
        $this->ruleResource->load($extendedRule, $ruleId, ExtendedCatalogRuleInterface::RULE_ID);
        return $extendedRule;
    }

    /**
     * @param int $ruleId
     * @return bool
     */
    public function deleteByCatalogRuleId($ruleId)
    {
        try {
            $this->ruleResource->deleteByCatalogRuleId($ruleId);
        } catch (\Exception $exception) {
            $this->logger->error(
                __(
                    'Can\'t delete Extended Rules by Catalog Price Rule ID = %1. Error: %2',
                    $ruleId,
                    $exception->getMessage()
                )
            );
            return false;
        }
        return true;
    }
}
