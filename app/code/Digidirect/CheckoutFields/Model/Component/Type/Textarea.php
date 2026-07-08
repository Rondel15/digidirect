<?php

namespace Digidirect\CheckoutFields\Model\Component\Type;

/**
 * Class Textarea
 * @package Digidirect\CheckoutFields\Model\Component\Type
 */
class Textarea extends AbstractType
{
    /**
     * @return string
     */
    protected function getComponent()
    {
        return 'Magento_Ui/js/form/element/textarea';
    }

    /**
     * @return string
     */
    protected function getElementTemplate()
    {
        return 'ui/form/element/textarea';
    }
}
