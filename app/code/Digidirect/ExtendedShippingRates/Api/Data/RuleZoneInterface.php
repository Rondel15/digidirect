<?php
namespace Digidirect\ExtendedShippingRates\Api\Data;

interface RuleZoneInterface
{
    const ZONE_COUNTRY = 'zone_country';
    const ZONE_STATE = 'zone_state';
    const ZONE_STATE_ID = 'zone_state_id';
    const USE_ZONES_FROM_STATE = 'use_zones_from_state';

    /**
     * Get Zone Country
     *
     * @return string|null
     */
    public function getZoneCountry();

    /**
     * Get Zone State
     *
     * @return string|null
     */
    public function getZoneState();

    /**
     * Get Zone State Id
     *
     * @return int|null
     */
    public function getZoneStateId();

    /**
     * Set Zone Country
     *
     * @param string $countryId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleZoneInterface
     */
    public function setZoneCountry($countryId);

    /**
     * Set Zone State
     *
     * @param string $state
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleZoneInterface
     */
    public function setZoneState($state);

    /**
     * Set Zone State Id
     *
     * @param int $stateId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RuleZoneInterface
     */
    public function setZoneStateId($stateId);
}
