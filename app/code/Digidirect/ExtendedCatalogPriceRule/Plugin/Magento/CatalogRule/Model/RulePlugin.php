<?php
namespace Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Model;

use Digidirect\ExtendedCatalogPriceRule\Api\Data\RuleDisplayMessageInterface;
use Digidirect\ExtendedCatalogPriceRule\Model\Magento\Rule\ExtendedRuleValidator;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\DataObject;

/**
 * Class RulePlugin
 * @package Digidirect\ExtendedCatalogPriceRule\Plugin\Magento\CatalogRule\Model
 */
class RulePlugin
{
    /**
     * @var ExtendedRuleValidator
     */
    protected $extendedRuleValidator;

    /**
     * RulePlugin constructor.
     * @param ExtendedRuleValidator $extendedRuleValidator
     */
    public function __construct(ExtendedRuleValidator $extendedRuleValidator)
    {
        $this->extendedRuleValidator = $extendedRuleValidator;
    }

    /**
     * @param Rule $subject
     * @param bool|string[] $result
     * @param DataObject $dataObject
     * @return bool|string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterValidateData(Rule $subject, $result, DataObject $dataObject)
    {
        $action = $dataObject->getData('simple_action');

        switch ($action) {
            case RuleDisplayMessageInterface::ACTION_CODE:
                return $this->validateDisplayMessageAction($dataObject);
            default:
                return empty($result) ? true : $result;
        }
    }

    /**
     * @param DataObject $dataObject
     * @return bool|string[]
     */
    public function validateDisplayMessageAction(DataObject $dataObject)
    {
        return $this->extendedRuleValidator->validateData($dataObject);
    }
}
