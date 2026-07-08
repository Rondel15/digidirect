<?php

namespace Digidirect\Collect\Api\Data;

/**
 * Collect Place Interface.
 *
 * @package Digidirect\Collect\Api\Data
 */
interface CollectPlaceInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    /**
     * Get Collect Place Id
     *
     * @return string|int
     */
    public function getId();

    /**
     * Get Collect Place Name
     *
     * @return string
     */
    public function getName();

    /**
     * Get Collect Place Address
     *
     * @return string
     */
    public function getAddress();

    /**
     * Get Collect Place Logitude
     *
     * @return string
     */
    public function getLongitude();

    /**
     * Get Collect Place Latitude
     *
     * @return string
     */
    public function getLatitude();
}
