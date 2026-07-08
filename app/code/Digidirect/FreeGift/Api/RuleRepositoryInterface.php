<?php

namespace Digidirect\FreeGift\Api;

interface RuleRepositoryInterface
{
    /**
     * Load Rule data by given Identity
     *
     * @param int $ruleId
     * @return Data\RuleInterface
     */
    public function getById($ruleId);

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return Data\RuleInterface
     */
    public function loadBySalesrule(\Magento\SalesRule\Model\Rule $rule);

    /**
     * @param Data\RuleInterface $rule
     * @return Data\RuleInterface
     */
    public function save(Data\RuleInterface $rule);

    /**
     * @param Data\RuleInterface $rule
     * @return bool
     */
    public function delete(Data\RuleInterface $rule);

    /**
     * Delete Rule by given Identity
     *
     * @param int $ruleId
     * @return bool
     */
    public function deleteById($ruleId);

    /**
     * Load Rule data by given Identity
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getPromoItemsByProduct(\Magento\Catalog\Model\Product $product);

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return []
     */
    public function getRulesDescriptionsByProduct(\Magento\Catalog\Model\Product $product);
}
