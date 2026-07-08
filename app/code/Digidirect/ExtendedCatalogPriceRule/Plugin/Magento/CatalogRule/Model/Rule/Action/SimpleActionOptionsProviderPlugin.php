<?php
namespace Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Model\Rule\Action;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Magento\CatalogRule\Model\Rule\Action\SimpleActionOptionsProvider;

/**
 * Class SimpleActionOptionsProviderPlugin
 * @package Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Model\Rule\Action
 */
class SimpleActionOptionsProviderPlugin
{
    /**
     * @param SimpleActionOptionsProvider $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToOptionArray(SimpleActionOptionsProvider $subject, array $result)
    {
        $result[] = [
            'label' => __('Display a message (no price affection)'),
            'value' => RuleDisplayMessageInterface::ACTION_CODE
        ];
        return $result;
    }
}
