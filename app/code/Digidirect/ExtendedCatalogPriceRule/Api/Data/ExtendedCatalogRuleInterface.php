<?php
namespace Digidirect\ExtendedCatalogPriceRule\Api\Data;

/**
 * Interface ExtendedCartPriceRuleTableInterface
 * @api
 */
interface ExtendedCatalogRuleInterface
{
    const ID = 'entity_id';
    const RULE_ID = 'original_rule_id';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getRuleId();

    /**
     * @param int $id
     * @return $this
     */
    public function setRuleId($id);
}
