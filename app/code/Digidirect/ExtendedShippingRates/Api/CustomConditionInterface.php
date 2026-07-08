<?php

namespace Digidirect\ExtendedShippingRates\Api;

use Magento\Framework\Model\AbstractModel;

/**
 * Interface CustomConditionInterface
 * @package Digidirect\ExtendedShippingRates\Api
 */
interface CustomConditionInterface
{
    /**
     * @return mixed
     */
    public function getLabel();

    /**
     * @param AbstractModel $model
     * @return mixed
     */
    public function validate(AbstractModel $model);
}
