<?php
namespace Digidirect\CheckoutFields\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Interface CustomFieldsInterface
 * @api
 */
interface CustomFieldsInterface
{
    /**
     *  Save custom checkout fields to custom tables
     *
     * @param anyType $params
     * @return bool
     * @throws LocalizedException
     */
    public function save($params);
}
