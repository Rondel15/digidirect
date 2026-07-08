<?php

namespace Digidirect\ExtendedShippingRates\Api\Data;

/**
 * Interface RuleAlternativeCodeInterface
 * @package Digidirect\ExtendedShippingRates\Api\Data
 */
interface RuleAlternativeCodeInterface
{
    const USED_ALT_CODE_SHIPPING_METHODS = 'used_alt_code_shipping_methods';
    const ALT_CODE_ACTION_OFFSET_BEGINS = 'alt_code_action_offset_begins';
    const ALT_CODE_ACTION_OFFSET_ENDS = 'alt_code_action_offset_ends';

    /**
     * Get used alternative code shipping methods
     *
     * @return string|null
     */
    public function getUsedAltCodeShippingMethods();

    /**
     * Get action offset begins
     *
     * @return int|null
     */
    public function getAltCodeActionOffsetBegins();

    /**
     * Get action offset ends
     *
     * @return int|null
     */
    public function getAltCodeActionOffsetEnds();

    /**
     * Set used alternative code shipping methods
     * @param string $methods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setUsedAltCodeShippingMethods($methods);

    /**
     * Set action offset begins
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setAltCodeActionOffsetBegins($offset);

    /**
     * Set action offset ends
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setAltCodeActionOffsetEnds($offset);
}
