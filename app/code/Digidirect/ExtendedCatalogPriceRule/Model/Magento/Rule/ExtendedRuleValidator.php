<?php
namespace Digidirect\ExtendedCatalogPriceRule\Model\Magento\Rule;

use Magento\Rule\Model\AbstractModel;

/**
 * Class ExtendedRuleValidator
 * @package Digidirect\ExtendedCatalogPriceRule\Model\Magento\Rule
 */
class ExtendedRuleValidator extends AbstractModel
{
    /**
     * Validation of Custom Extended Catalog Price Rules can be added into this method.
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string[]
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];
        if ($dataObject->getData('discount_amount') < 0) {
            $result[] = __('Discount value should be 0 or greater.');
        }

        $parentResult = parent::validateData($dataObject);
        if (is_array($parentResult)) {
            $result = array_merge($result, $parentResult);
        }

        return empty($result) ? true : $result;
    }

    /**
     * @return false
     */
    public function getConditionsInstance()
    {
        return false;
    }

    /**
     * @return false
     */
    public function getActionsInstance()
    {
        return false;
    }
}
