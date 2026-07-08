<?php

namespace Digidirect\ExtendedShippingRates\Api\Data;

/**
 * Interface RuleAlternativeTitleInterface
 * @package Digidirect\ExtendedShippingRates\Api\Data
 */
interface RuleAlternativeTitleInterface
{
    const USED_ALT_TITLE_SHIPPING_METHODS = 'used_alt_title_shipping_methods';
    const ACTION_OFFSET_BEGINS = 'action_offset_begins';
    const ACTION_OFFSET_ENDS = 'action_offset_ends';

    /**
     * Get used alternative title shipping methods
     *
     * @return string|null
     */
    public function getUsedAltTitleShippingMethods();

    /**
     * Get action offset begins
     *
     * @return int|null
     */
    public function getActionOffsetBegins();

    /**
     * Get action offset ends
     *
     * @return int|null
     */
    public function getActionOffsetEnds();

    /**
     * Set used alternative title shipping methods
     * @param string $methods
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setUsedAltTitleShippingMethods($methods);

    /**
     * Set action offset begins
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setActionOffsetBegins($offset);

    /**
     * Set action offset ends
     * @param int $offset
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleAlternativeTitleInterface
     */
    public function setActionOffsetEnds($offset);
}
