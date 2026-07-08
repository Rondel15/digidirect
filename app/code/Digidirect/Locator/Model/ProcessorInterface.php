<?php
namespace Digidirect\Locator\Model;

/**
 * Interface ProcessorInterface
 * @package Digidirect\Locator\Model
 */
interface ProcessorInterface
{

    /**
     *  Must return array format of
     *  [ id => array(
     *              'name' => value,
     *              'address' => value,
     *              'city' => value,
     *              'state' => value,
     *              'postcode' => value,
     *              'longitude' => value,
     *              'latitude' => value
     *          )
     * ]
     * @param array $data
     * @param array $searchParams
     * @return array
     */
    public function process($data, $searchParams);
}
