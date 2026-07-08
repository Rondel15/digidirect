<?php

declare(strict_types=1);

namespace Digidirect\Digi\Plugin\Magento\CustomAttributeManagement\Block;

use Magento\CustomAttributeManagement\Block\Form as FormSubject;
use Magento\Eav\Model\Attribute;

/**
 * Class Form
 *
 * @author Michael Marchanka <michail.marchenko@Digidirect.com>
 */
class Form
{
    private const AIPP_NUMBER_FRONTEND_INPUT = 'aipp-number';
    private const AIPP_NUMBER_ATTR_CODE = 'aipp_number';

    /**
     * @param FormSubject $subject
     * @param Attribute $attribute
     *
     * @return Attribute[]
     */
    public function beforeGetAttributeHtml(FormSubject $subject, Attribute $attribute)
    {
        if (self::AIPP_NUMBER_ATTR_CODE == $attribute->getAttributeCode()) {
            $attribute->setFrontendInput(self::AIPP_NUMBER_FRONTEND_INPUT);
        }

        return [$attribute];
    }
}
