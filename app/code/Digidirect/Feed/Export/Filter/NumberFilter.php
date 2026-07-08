<?php

namespace Digidirect\Feed\Export\Filter;

class NumberFilter
{
    /**
     * Ceil
     *
     * Rounds an output up to the nearest integer.
     *
     * @param string $input
     * @return number
     */
    public function ceil($input)
    {
        return ceil($input);
    }

    /**
     * Floor
     *
     * Rounds an output down to the nearest integer.
     *
     * @param string $input
     * @return number
     */
    public function floor($input)
    {
        return floor($input);
    }

    /**
     * Round
     *
     * Rounds the output to the nearest integer or specified number of decimals.
     *
     * @param string $input
     * @param number $precision
     * @return number
     */
    public function round($input, $precision)
    {
        return round($input, $precision);
    }

    /**
     * Number Format
     *
     * Format
     *
     * @param string $input
     * @param int $decimals
     * @param string $decPoint
     * @param string $thousandsSep
     * @return string
     */
    public function numberFormat($input, $decimals = 0, $decPoint = '.', $thousandsSep = ',')
    {
        return number_format($input, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Price Format
     *
     * @param string $input
     * @return string
     */
    public function price($input)
    {
        return number_format($input, 2, '.', '');
    }
}
