<?php
namespace Digidirect\CollectAbstractEntity\Api\Data\CollectFields;

/**
 * Interface Constants
 * @package Digidirect\CollectAbstractEntity\Api\Data\CollectFields
 */
interface Constants
{
    const COLLECT_FIELD_POSTCODE = 'postcode';
    const COLLECT_FIELD_LONGITUDE = 'longitude';
    const COLLECT_FIELD_LATITUDE = 'latitude';
    const COLLECT_FIELD_ADDRESS = 'address';
    const COLLECT_FIELD_NAME = 'name';

    const COLLECT_FIELDS_ALL = [
        self::COLLECT_FIELD_POSTCODE,
        self::COLLECT_FIELD_LONGITUDE,
        self::COLLECT_FIELD_LATITUDE,
        self::COLLECT_FIELD_ADDRESS,
        self::COLLECT_FIELD_NAME,
    ];
}
